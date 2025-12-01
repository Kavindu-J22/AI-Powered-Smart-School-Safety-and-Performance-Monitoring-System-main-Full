<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\Management\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\StudentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceApiController extends Controller
{
    protected $attendanceRepository;
    protected $studentRepository;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
        StudentRepositoryInterface $studentRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->studentRepository = $studentRepository;
    }

    /**
     * Handle RFID scan from Arduino WiFi device
     *
     * Expected payload:
     * {
     *   "student_data": "STUDENT_CODE|FIRST_NAME|LAST_NAME|GRADE|CLASS|DATE",
     *   "card_uid": "A1B2C3D4",
     *   "device_id": "ATTENDANCE_READER_01",
     *   "timestamp": "2025-10-09 14:30:45",
     *   "device_time": "2025-10-09 14:30:45"
     * }
     */
    public function rfidScan(Request $request): JsonResponse
    {
        try {
            // Validate incoming data
            $validated = $request->validate([
                'student_data' => 'required|string',
                'card_uid' => 'required|string',
                'device_id' => 'required|string',
                'timestamp' => 'nullable|date',
                'device_time' => 'nullable|date'
            ]);

            Log::info('RFID Scan received from device', [
                'device_id' => $validated['device_id'],
                'card_uid' => $validated['card_uid'],
                'timestamp' => $validated['timestamp'] ?? 'not provided'
            ]);

            // Parse student data from RFID tag
            // Format: STUDENT_CODE|FIRST_NAME|LAST_NAME|GRADE|CLASS|DATE
            $studentData = explode('|', $validated['student_data']);

            if (count($studentData) < 1) {
                Log::warning('Invalid student data format', ['data' => $validated['student_data']]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid student data format on RFID tag'
                ], 400);
            }

            $studentCode = $studentData[0];

            // Find student by code
            $student = $this->studentRepository->findByCode($studentCode);

            if (!$student) {
                Log::warning('Student not found', ['code' => $studentCode]);
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found in database',
                    'student_code' => $studentCode
                ], 404);
            }

            // Use device timestamp if provided, otherwise use server time
            $scanTime = isset($validated['timestamp'])
                ? Carbon::parse($validated['timestamp'])
                : now();

            // Check if already scanned recently (prevent duplicate scans within 3 seconds)
            $cacheKey = "rfid_scan_{$student->student_id}_{$scanTime->format('Y-m-d')}";
            $lastScanTime = Cache::get($cacheKey);

            if ($lastScanTime && now()->diffInSeconds($lastScanTime) < 3) {
                Log::info('Duplicate scan prevented', ['student_id' => $student->student_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate scan - please wait a few seconds',
                    'student_name' => $student->first_name . ' ' . $student->last_name
                ], 429); // Too Many Requests
            }

            // Update cache with current scan time
            Cache::put($cacheKey, now(), now()->addSeconds(5));

            // Get today's attendance record
            $todayAttendance = $this->attendanceRepository->getTodayAttendance($student->student_id);

            // Determine action: check-in or check-out
            if (!$todayAttendance || $todayAttendance->status === 'absent') {
                // Check in
                $attendance = $this->attendanceRepository->checkIn($student->student_id, [
                    'check_in_time' => $scanTime,
                    'device_id' => $validated['device_id'],
                    'nfc_tag_id' => $validated['card_uid'],
                    'notes' => 'WiFi RFID Device Scan'
                ]);

                Log::info('Student checked in', [
                    'student_id' => $student->student_id,
                    'student_code' => $studentCode,
                    'device_id' => $validated['device_id'],
                    'is_late' => $attendance->is_late
                ]);

                return response()->json([
                    'success' => true,
                    'action' => 'check_in',
                    'message' => 'Student checked in successfully',
                    'data' => [
                        'student_id' => $student->student_id,
                        'student_code' => $studentCode,
                        'student_name' => $student->first_name . ' ' . $student->last_name,
                        'grade' => $student->grade_level,
                        'class' => $student->schoolClass->class_name ?? 'N/A',
                        'time' => $attendance->check_in_time->format('H:i:s'),
                        'date' => $attendance->attendance_date->format('Y-m-d'),
                        'is_late' => $attendance->is_late,
                        'status' => $attendance->status
                    ]
                ]);
            } elseif ($todayAttendance->status === 'present' && !$todayAttendance->check_out_time) {
                // Check out
                $attendance = $this->attendanceRepository->checkOut($student->student_id, [
                    'check_out_time' => $scanTime,
                    'notes' => 'WiFi RFID Device Scan'
                ]);

                Log::info('Student checked out', [
                    'student_id' => $student->student_id,
                    'student_code' => $studentCode,
                    'device_id' => $validated['device_id'],
                    'duration' => $attendance->duration
                ]);

                return response()->json([
                    'success' => true,
                    'action' => 'check_out',
                    'message' => 'Student checked out successfully',
                    'data' => [
                        'student_id' => $student->student_id,
                        'student_code' => $studentCode,
                        'student_name' => $student->first_name . ' ' . $student->last_name,
                        'grade' => $student->grade_level,
                        'class' => $student->schoolClass->class_name ?? 'N/A',
                        'check_in' => $attendance->check_in_time->format('H:i:s'),
                        'check_out' => $attendance->check_out_time->format('H:i:s'),
                        'date' => $attendance->attendance_date->format('Y-m-d'),
                        'duration' => $attendance->duration,
                        'status' => $attendance->status
                    ]
                ]);
            } else {
                // Already checked out
                Log::info('Student already checked out today', [
                    'student_id' => $student->student_id,
                    'student_code' => $studentCode
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Student already checked out today',
                    'data' => [
                        'student_name' => $student->first_name . ' ' . $student->last_name,
                        'check_in' => $todayAttendance->check_in_time?->format('H:i:s'),
                        'check_out' => $todayAttendance->check_out_time?->format('H:i:s')
                    ]
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('RFID scan validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('RFID scan error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error processing attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new attendance device
     */
    public function registerDevice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|string|max:50',
                'device_name' => 'nullable|string|max:100',
                'device_type' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:100',
                'ip_address' => 'nullable|ip',
                'mac_address' => 'nullable|string|max:17'
            ]);

            // Store device info in cache (or database if you create a devices table)
            $deviceKey = "device_{$validated['device_id']}";
            Cache::put($deviceKey, $validated, now()->addDays(30));

            Log::info('Device registered', $validated);

            return response()->json([
                'success' => true,
                'message' => 'Device registered successfully',
                'device_id' => $validated['device_id'],
                'server_time' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Device registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register device'
            ], 500);
        }
    }

    /**
     * Device health check / ping
     */
    public function devicePing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'status' => 'nullable|string',
            'uptime' => 'nullable|integer',
            'free_memory' => 'nullable|integer'
        ]);

        // Update device last seen time
        $deviceKey = "device_{$validated['device_id']}_last_seen";
        Cache::put($deviceKey, now(), now()->addHours(1));

        // Store device status
        if (isset($validated['status'])) {
            $statusKey = "device_{$validated['device_id']}_status";
            Cache::put($statusKey, $validated, now()->addMinutes(5));
        }

        return response()->json([
            'success' => true,
            'server_time' => now()->format('Y-m-d H:i:s'),
            'message' => 'Pong'
        ]);
    }

    /**
     * Sync pending attendance records from device SD card
     *
     * Expected payload:
     * {
     *   "device_id": "ATTENDANCE_READER_01",
     *   "records": [
     *     {
     *       "timestamp": "2025-10-09 14:30:45",
     *       "student_data": "STU001|John|Doe|10|A|2025-09-01",
     *       "card_uid": "A1B2C3D4"
     *     },
     *     ...
     *   ]
     * }
     */
    public function syncPendingRecords(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|string',
                'records' => 'required|array',
                'records.*.timestamp' => 'required|date',
                'records.*.student_data' => 'required|string',
                'records.*.card_uid' => 'required|string'
            ]);

            $synced = 0;
            $failed = 0;
            $errors = [];

            foreach ($validated['records'] as $index => $record) {
                try {
                    // Parse student data
                    $studentData = explode('|', $record['student_data']);
                    $studentCode = $studentData[0] ?? '';

                    if (empty($studentCode)) {
                        $failed++;
                        $errors[] = "Record {$index}: Invalid student data";
                        continue;
                    }

                    // Find student
                    $student = $this->studentRepository->findByCode($studentCode);
                    if (!$student) {
                        $failed++;
                        $errors[] = "Record {$index}: Student not found ({$studentCode})";
                        continue;
                    }

                    $scanTime = Carbon::parse($record['timestamp']);
                    $scanDate = $scanTime->format('Y-m-d');

                    // Check if attendance already exists for this date
                    $existingAttendance = $this->attendanceRepository->getAttendanceByDate(
                        $student->student_id,
                        Carbon::parse($scanDate)
                    );

                    if (!$existingAttendance || $existingAttendance->status === 'absent') {
                        // Check in
                        $this->attendanceRepository->checkIn($student->student_id, [
                            'check_in_time' => $scanTime,
                            'device_id' => $validated['device_id'],
                            'nfc_tag_id' => $record['card_uid'],
                            'notes' => 'Synced from device SD card'
                        ]);
                        $synced++;
                    } elseif (!$existingAttendance->check_out_time) {
                        // Check out
                        $this->attendanceRepository->checkOut($student->student_id, [
                            'check_out_time' => $scanTime,
                            'notes' => 'Synced from device SD card'
                        ]);
                        $synced++;
                    } else {
                        // Already complete
                        $errors[] = "Record {$index}: Already complete ({$studentCode})";
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Record {$index}: " . $e->getMessage();
                    Log::error("Sync record error: " . $e->getMessage(), ['record' => $record]);
                }
            }

            Log::info('Attendance sync completed', [
                'device_id' => $validated['device_id'],
                'total' => count($validated['records']),
                'synced' => $synced,
                'failed' => $failed
            ]);

            return response()->json([
                'success' => true,
                'message' => "Synced {$synced} records, {$failed} failed",
                'synced' => $synced,
                'failed' => $failed,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device configuration
     */
    public function getDeviceConfig(Request $request): JsonResponse
    {
        $deviceId = $request->get('device_id');

        $config = [
            'server_time' => now()->format('Y-m-d H:i:s'),
            'school_start_time' => '08:00:00',
            'school_end_time' => '15:00:00',
            'late_threshold' => '08:15:00',  // Students arriving after this are marked late
            'scan_cooldown' => 3000,  // milliseconds
            'sync_interval' => 300,  // seconds (5 minutes)
            'timezone' => config('app.timezone'),
        ];

        if ($deviceId) {
            $deviceKey = "device_{$deviceId}";
            $deviceInfo = Cache::get($deviceKey);
            if ($deviceInfo) {
                $config['device_info'] = $deviceInfo;
            }
        }

        return response()->json([
            'success' => true,
            'config' => $config
        ]);
    }

    /**
     * Get today's attendance (for authenticated users)
     */
    public function getTodayAttendance(Request $request): JsonResponse
    {
        $attendances = $this->attendanceRepository->getToday();

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'count' => $attendances->count()
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        $stats = $this->attendanceRepository->getStatistics($date);

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date' => $date->format('Y-m-d')
        ]);
    }

    /**
     * Get attendance report
     */
    public function getReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:school_classes,class_id',
            'status' => 'nullable|in:present,absent,late,excused'
        ]);

        $filters = [
            'start_date' => Carbon::parse($validated['start_date']),
            'end_date' => Carbon::parse($validated['end_date']),
            'class_id' => $validated['class_id'] ?? null,
            'status' => $validated['status'] ?? null
        ];

        $attendances = $this->attendanceRepository->getReport($filters);

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'filters' => $filters
        ]);
    }

    /**
     * Get student attendance history
     */
    public function getStudentAttendance(Request $request, $studentId): JsonResponse
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : Carbon::now()->subDays(30);

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))
            : Carbon::today();

        $attendances = $this->attendanceRepository->getStudentAttendance(
            $studentId,
            $startDate,
            $endDate
        );

        $percentage = $this->attendanceRepository->getStudentAttendancePercentage(
            $studentId,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => [
                'attendances' => $attendances,
                'percentage' => $percentage,
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]
        ]);
    }
}
