<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Repositories\Interfaces\Admin\Management\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\StudentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FaceRecognitionController extends Controller
{
    const FACE_API_URL = 'http://127.0.0.1:5004';
    const LAST_SCAN_KEY = 'face_last_scan';

    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected StudentRepositoryInterface $studentRepository,
    ) {}

    // ── Health check ─────────────────────────────────────────────────────────

    public function health(): JsonResponse
    {
        try {
            $resp = Http::timeout(3)->get(self::FACE_API_URL . '/health');
            return response()->json($resp->json(), $resp->status());
        } catch (\Exception $e) {
            return response()->json(['status' => 'unavailable', 'error' => $e->getMessage()], 503);
        }
    }

    /** Return current attendance mode from settings. */
    public function currentMode(): JsonResponse
    {
        $setting = Setting::first();
        return response()->json(['mode' => $setting->attendance_mode ?? 'rfid']);
    }

    // ── Face Registration proxy ───────────────────────────────────────────────

    /**
     * Start a face capture session for a student.
     * Body: { student_id, student_name, capture_count }
     */
    public function startRegistration(Request $request): JsonResponse
    {
        $request->validate([
            'student_id'   => 'required|integer',
            'student_name' => 'required|string|max:255',
            'capture_count' => 'nullable|integer|min:10|max:100',
        ]);

        try {
            $resp = Http::timeout(10)->post(self::FACE_API_URL . '/api/registration/start', [
                'student_id'   => $request->student_id,
                'student_name' => $request->student_name,
                'capture_count' => $request->capture_count ?? 40,
            ]);
            return response()->json($resp->json(), $resp->status());
        } catch (\Exception $e) {
            Log::error('Face API start registration error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Face API unavailable: ' . $e->getMessage()], 503);
        }
    }

    /**
     * Send a single captured frame to the registration session.
     * Body: { session_id, image (base64 data-url) }
     */
    public function captureFrame(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'image'      => 'required|string',
        ]);

        try {
            $resp = Http::timeout(10)->post(self::FACE_API_URL . '/api/registration/capture', [
                'session_id' => $request->session_id,
                'image'      => $request->image,
            ]);
            return response()->json($resp->json(), $resp->status());
        } catch (\Exception $e) {
            Log::error('Face API capture error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Face API unavailable: ' . $e->getMessage()], 503);
        }
    }

    // ── Training proxy ────────────────────────────────────────────────────────

    /** Train / retrain the face model for a single student. */
    public function trainStudent(Request $request, int $student_id): JsonResponse
    {
        set_time_limit(300); // Allow up to 5 min for training
        try {
            $resp = Http::timeout(240)->post(self::FACE_API_URL . "/api/training/train/{$student_id}");
            return response()->json($resp->json(), $resp->status());
        } catch (\Exception $e) {
            Log::error('Face API training error', ['student_id' => $student_id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Face API unavailable: ' . $e->getMessage()], 503);
        }
    }

    // ── Attendance Recognition ────────────────────────────────────────────────

    /**
     * Receive a webcam frame, run face recognition, and save attendance.
     * Body: { image (base64 data-url) }
     * Returns the full attendance result including student info.
     */
    public function recognize(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            $resp = Http::timeout(15)->post(self::FACE_API_URL . '/api/attendance/recognize', [
                'image' => $request->image,
            ]);

            if (!$resp->successful()) {
                return response()->json($resp->json(), $resp->status());
            }

            $data = $resp->json();

            // The Python service returns { success, total_faces, marked: [{student_id, ...}] }
            // NOT { recognized, student_id } — check the correct fields.
            $marked = $data['marked'] ?? [];
            $faces  = $data['faces']  ?? [];   // [{bbox,confidence,is_recognized,student_name}]

            if (empty($data['success']) || empty($marked)) {
                $msg = $data['message'] ?? 'No face recognised';
                // Provide more useful message when faces were seen but not matched
                if (!empty($data['total_faces']) && $data['total_faces'] > 0) {
                    $msg = 'Face detected but not recognised. Please re-register or retrain.';
                }
                return response()->json([
                    'success'    => false,
                    'recognized' => false,
                    'message'    => $msg,
                    'faces'      => $faces,
                ]);
            }

            // student_id may be stored as "DASH_123" or plain "123" — strip prefix
            $rawStudentId = $marked[0]['student_id'] ?? null;
            if (!$rawStudentId) {
                return response()->json(['success' => false, 'message' => 'Invalid student_id in recognition result']);
            }
            $studentId = (int) preg_replace('/^DASH_/i', '', (string) $rawStudentId);
            $pythonConfidence = $marked[0]['confidence'] ?? ($data['confidence'] ?? null);

            // ── Find student in DB ───────────────────────────────────────────
            $student = $this->studentRepository->getById($studentId);
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => "Recognised student_id {$studentId} not found in database",
                ], 404);
            }

            // ── Duplicate debounce (3 s) ─────────────────────────────────────
            $debounceKey = "face_debounce_{$studentId}";
            if (Cache::has($debounceKey)) {
                return response()->json([
                    'success'    => false,
                    'recognized' => true,
                    'message'    => 'Duplicate scan — please wait',
                    'student_name' => $student->first_name . ' ' . $student->last_name,
                ], 429);
            }
            Cache::put($debounceKey, true, now()->addSeconds(3));

            $studentName = $student->first_name . ' ' . $student->last_name;
            $today = Carbon::today();

            // ── Load today's attendance ───────────────────────────────────────
            $todayAttendance = $this->attendanceRepository->getTodayAttendance($studentId);

            // ── Already fully recorded ───────────────────────────────────────
            if ($todayAttendance && $todayAttendance->check_in_time && $todayAttendance->check_out_time) {
                $result = [
                    'student_id'   => $studentId,
                    'student_name' => $studentName,
                    'student_code' => $student->student_code,
                    'grade'        => $student->grade_level,
                    'class'        => $student->schoolClass->class_name ?? 'N/A',
                    'action'       => 'already_complete',
                    'check_in'     => $todayAttendance->check_in_time?->format('H:i:s') ?? '—',
                    'check_out'    => $todayAttendance->check_out_time?->format('H:i:s') ?? '—',
                    'time'         => now()->format('H:i:s'),
                    'scanned_at'   => now()->toIso8601String(),
                    'success'      => false,
                    'source'       => 'face',
                    'confidence'   => $pythonConfidence,
                ];
                Cache::put(self::LAST_SCAN_KEY, $result, now()->addMinutes(10));
                return response()->json(['success' => false, 'action' => 'already_complete', 'data' => $result, 'faces' => $faces], 409);
            }

            // ── Check In ─────────────────────────────────────────────────────
            if (!$todayAttendance || !$todayAttendance->check_in_time) {
                $attendance = $this->attendanceRepository->checkIn($studentId, [
                    'notes' => 'Face Recognition',
                ]);
                $result = $this->buildScanData($student, $attendance, 'check_in', $pythonConfidence);
                Cache::put(self::LAST_SCAN_KEY, $result, now()->addMinutes(10));
                return response()->json(['success' => true, 'action' => 'check_in', 'data' => $result, 'faces' => $faces]);
            }

            // ── Check Out ────────────────────────────────────────────────────
            $attendance = $this->attendanceRepository->checkOut($studentId, [
                'notes' => 'Face Recognition',
            ]);
            $result = $this->buildScanData($student, $attendance, 'check_out', $pythonConfidence);
            Cache::put(self::LAST_SCAN_KEY, $result, now()->addMinutes(10));
            return response()->json(['success' => true, 'action' => 'check_out', 'data' => $result, 'faces' => $faces]);
        } catch (\Exception $e) {
            Log::error('Face recognition attendance error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Recognition error: ' . $e->getMessage()], 500);
        }
    }

    /** Return the most recent face-scan result (polled by UI). */
    public function getLastScan(): JsonResponse
    {
        $data = Cache::get(self::LAST_SCAN_KEY);
        return response()->json(['found' => (bool) $data, 'data' => $data]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildScanData($student, $attendance, string $action, ?float $confidence): array
    {
        $data = [
            'student_id'   => $student->student_id,
            'student_code' => $student->student_code,
            'student_name' => $student->first_name . ' ' . $student->last_name,
            'grade'        => $student->grade_level,
            'class'        => $student->schoolClass->class_name ?? 'N/A',
            'action'       => $action,
            'scanned_at'   => now()->toIso8601String(),
            'success'      => true,
            'source'       => 'face',
            'confidence'   => $confidence ? round($confidence * 100, 1) . '%' : null,
        ];

        if ($action === 'check_in') {
            $data['time']    = $attendance->check_in_time->format('H:i:s');
            $data['is_late'] = $attendance->is_late ?? false;
        } else {
            $data['time']     = $attendance->check_out_time->format('H:i:s');
            $checkin = $attendance->check_in_time;
            $checkout = $attendance->check_out_time;
            if ($checkin && $checkout) {
                $mins = (int) $checkin->diffInMinutes($checkout);
                $data['duration'] = $mins >= 60
                    ? floor($mins / 60) . 'h ' . ($mins % 60) . 'm'
                    : $mins . 'm';
            }
        }
        return $data;
    }
}
