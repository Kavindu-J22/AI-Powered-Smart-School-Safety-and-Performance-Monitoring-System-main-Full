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
use Illuminate\Support\Facades\Log;

class RfidController extends Controller
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected StudentRepositoryInterface $studentRepository,
    ) {}

    /**
     * Unified endpoint the Python serial bridge always posts to.
     *
     * Payload: { "uid": "A1B2C3D4", "device_id": "DOOR_01" }
     *
     * Logic:
     *   1. If there is an active enrollment token in the cache → store the UID
     *      there and return {mode:"enrollment"}.
     *   2. Otherwise process as an attendance scan, delegating to the same logic
     *      used by AttendanceApiController@rfidUidScan.
     */
    public function bridgeScan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uid'       => 'required|string|max:50',
            'device_id' => 'required|string|max:100',
        ]);

        $uid      = strtoupper(trim($validated['uid']));
        $deviceId = $validated['device_id'];

        // ── 0. Attendance-mode gate ──────────────────────────────────────────
        // Allow RFID through for enrollment even when mode is face_recognition.
        $enrollmentKey = Cache::get('rfid_active_enrollment_key');
        if (!$enrollmentKey) {
            $setting = Setting::first();
            if ($setting && $setting->attendance_mode === 'face_recognition') {
                return response()->json([
                    'success' => false,
                    'message' => 'RFID attendance is disabled. Current mode: Facial Recognition.',
                    'mode'    => 'face_recognition',
                ], 423);
            }
        }

        // ── 1. Check for an active enrollment session ────────────────────────
        if ($enrollmentKey) {
            Cache::put($enrollmentKey, $uid, now()->addMinutes(5));
            Log::info('RFID enrollment UID captured', ['uid' => $uid, 'key' => $enrollmentKey]);

            return response()->json([
                'mode'    => 'enrollment',
                'uid'     => $uid,
                'message' => 'UID captured for enrollment',
            ]);
        }

        // ── 2. Treat as attendance scan ───────────────────────────────────────
        return $this->processAttendanceScan($uid, $deviceId);
    }

    /**
     * Browser polls this every 1.5 s while the RFID enrollment modal is open.
     *
     * Returns { "found": false }  while waiting.
     * Returns { "found": true, "uid": "A1B2C3D4" } once the bridge has posted.
     */
    public function pollEnrollment(Request $request, string $token): JsonResponse
    {
        $cacheKey = "rfid_enrollment_{$token}";
        $uid      = Cache::get($cacheKey);

        if ($uid) {
            return response()->json(['found' => true, 'uid' => $uid]);
        }

        return response()->json(['found' => false]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function processAttendanceScan(string $uid, string $deviceId): JsonResponse
    {
        $student = $this->studentRepository->findByRfidUid($uid);

        if (! $student) {
            Log::warning('RFID scan: student not found for UID', ['uid' => $uid]);
            return response()->json([
                'success' => false,
                'message' => 'No student is assigned to this wristband',
                'uid'     => $uid,
            ], 404);
        }

        $now          = now();
        $studentId    = $student->student_id;
        $studentName  = $student->first_name . ' ' . $student->last_name;

        // ── Duplicate scan guard (3-second same-card debounce) ────────────────
        $debounceKey = "rfid_debounce_{$studentId}";
        if (Cache::has($debounceKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate scan — please wait a moment',
                'student_name' => $studentName,
            ], 429);
        }
        Cache::put($debounceKey, true, now()->addSeconds(3));

        $todayAttendance = $this->attendanceRepository->getTodayAttendance($studentId);

        // ── Check-in ──────────────────────────────────────────────────────────
        if (! $todayAttendance || $todayAttendance->status === 'absent') {
            $attendance = $this->attendanceRepository->checkIn($studentId, [
                'check_in_time' => $now,
                'device_id'     => $deviceId,
                'nfc_tag_id'    => $uid,
                'notes'         => 'RFID Serial Bridge',
            ]);

            // Store last scan result so the UI can poll it
            $this->storeLastScanResult($studentId, $studentName, 'check_in', $attendance, $student);

            return response()->json([
                'success' => true,
                'action'  => 'check_in',
                'message' => 'Checked in successfully',
                'data'    => $this->buildScanData($student, $attendance, 'check_in'),
            ]);
        }

        // ── Check-out (only if not yet checked out) ───────────────────────────
        if ($todayAttendance->status === 'present' && ! $todayAttendance->check_out_time) {
            // Enforce 5-minute minimum stay before checkout
            $cooldownKey = "rfid_checkout_cooldown_{$studentId}";
            if (Cache::has($cooldownKey)) {
                $unlockAt = Cache::get($cooldownKey);
                $remaining = max(0, (int) now()->diffInSeconds($unlockAt, false));
                return response()->json([
                    'success'      => false,
                    'message'      => "Cannot check out yet — please wait {$remaining} more seconds",
                    'student_name' => $studentName,
                ], 429);
            }

            $attendance = $this->attendanceRepository->checkOut($studentId, [
                'check_out_time' => $now,
                'notes'          => 'RFID Serial Bridge',
            ]);

            // 5-minute cooldown after a checkout (prevents immediate re-checkout
            // if the student accidentally taps again)
            Cache::put($cooldownKey, now()->addMinutes(5), now()->addMinutes(5));

            $this->storeLastScanResult($studentId, $studentName, 'check_out', $attendance, $student);

            return response()->json([
                'success' => true,
                'action'  => 'check_out',
                'message' => 'Checked out successfully',
                'data'    => $this->buildScanData($student, $attendance, 'check_out'),
            ]);
        }

        // ── Already fully recorded today ──────────────────────────────────────
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
        ];
        Cache::put('rfid_last_scan', $result, now()->addMinutes(10));

        return response()->json([
            'success'      => false,
            'action'       => 'already_complete',
            'message'      => 'Attendance already fully recorded for today',
            'student_name' => $studentName,
        ], 409);
    }

    private function buildScanData($student, $attendance, string $action): array
    {
        $data = [
            'student_id'   => $student->student_id,
            'student_code' => $student->student_code,
            'student_name' => $student->first_name . ' ' . $student->last_name,
            'grade'        => $student->grade_level,
            'class'        => $student->schoolClass->class_name ?? 'N/A',
            'date'         => $attendance->attendance_date->format('Y-m-d'),
            'status'       => $attendance->status,
            'action'       => $action,
        ];

        if ($action === 'check_in') {
            $data['time']    = $attendance->check_in_time->format('H:i:s');
            $data['is_late'] = $attendance->is_late;
        } else {
            $data['time']     = $attendance->check_out_time->format('H:i:s');
            $data['duration'] = $attendance->duration ?? null;
        }

        return $data;
    }

    private function storeLastScanResult(
        int $studentId,
        string $studentName,
        string $action,
        $attendance,
        $student
    ): void {
        // Store per-device-namespace "last scan" so the UI can show it
        $result = [
            'student_id'   => $studentId,
            'student_name' => $studentName,
            'student_code' => $student->student_code,
            'grade'        => $student->grade_level,
            'class'        => $student->schoolClass->class_name ?? 'N/A',
            'action'       => $action,
            'time'         => now()->format('H:i:s'),
            'scanned_at'   => now()->toIso8601String(),
            'success'      => true,
            'is_late'      => $attendance->is_late ?? false,
            'duration'     => $attendance->duration ?? null,
        ];

        Cache::put('rfid_last_scan', $result, now()->addMinutes(10));
    }
}
