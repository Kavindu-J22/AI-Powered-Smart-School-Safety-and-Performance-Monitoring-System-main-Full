<?php

namespace App\Http\Controllers\Admin\Management;

use App\DataTables\Admin\Management\AttendanceDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\Management\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\StudentRepositoryInterface;
use App\Services\ArduinoNFCService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceRepository;
    protected $studentRepository;
    protected $arduinoService;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
        StudentRepositoryInterface $studentRepository,
        ArduinoNFCService $arduinoService
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->studentRepository = $studentRepository;
        $this->arduinoService = $arduinoService;
    }

    /**
     * Display attendance list with DataTable
     */
    public function index(AttendanceDataTable $dataTable, Request $request)
    {
        Session::put('title', 'Attendance Management');
        return $dataTable->render('admin.pages.management.attendance.index');
    }

    /**
     * Display the specified attendance record
     */
    public function show(string $id)
    {
        $attendance = $this->attendanceRepository->getById($id);

        if (!$attendance) {
            return redirect()->route('admin.management.attendance.index')
                ->with('error', 'Attendance record not found.');
        }

        return view('admin.pages.management.attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified attendance record
     */
    public function edit(string $id)
    {
        $attendance = $this->attendanceRepository->getById($id);

        if (!$attendance) {
            return redirect()->route('admin.management.attendance.index')
                ->with('error', 'Attendance record not found.');
        }

        return view('admin.pages.management.attendance.edit', compact('attendance'));
    }

    /**
     * Update the specified attendance record
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string|max:500'
        ]);

        $attendance = $this->attendanceRepository->getById($id);

        if (!$attendance) {
            return redirect()->route('admin.management.attendance.index')
                ->with('error', 'Attendance record not found.');
        }

        try {
            $updateData = [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null
            ];

            if ($validated['check_in_time']) {
                $date = $attendance->attendance_date->format('Y-m-d');
                $updateData['check_in_time'] = Carbon::parse($date . ' ' . $validated['check_in_time']);
            }

            if ($validated['check_out_time']) {
                $date = $attendance->attendance_date->format('Y-m-d');
                $updateData['check_out_time'] = Carbon::parse($date . ' ' . $validated['check_out_time']);
            }

            $this->attendanceRepository->update($id, $updateData);

            return redirect()->route('admin.management.attendance.index')
                ->with('success', 'Attendance record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Attendance update error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(string $id)
    {
        try {
            $attendance = $this->attendanceRepository->getById($id);

            if (!$attendance) {
                return redirect()->route('admin.management.attendance.index')
                    ->with('error', 'Attendance record not found.');
            }

            $this->attendanceRepository->delete($id);

            return redirect()->route('admin.management.attendance.index')
                ->with('success', 'Attendance record deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Attendance delete error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Display real-time attendance dashboard
     */
    public function dashboard()
    {
        $today = Carbon::today();

        // Get today's statistics
        $stats = $this->attendanceRepository->getStatistics($today);

        // Get recent check-ins (last 20)
        $recentCheckIns = $this->attendanceRepository->getToday()
            ->sortByDesc('check_in_time')
            ->take(20);

        return view('admin.pages.management.attendance.dashboard', compact('stats', 'recentCheckIns'));
    }

    /**
     * Show manual attendance form
     */
    public function create()
    {
        return view('admin.pages.management.attendance.create');
    }

    /**
     * Store manual attendance
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_code' => 'required|string',
                'attendance_type' => 'required|in:check_in,check_out,absent',
                'date' => 'nullable|date',
                'check_in_time' => 'nullable|date_format:H:i',
                'check_out_time' => 'nullable|date_format:H:i',
                'notes' => 'nullable|string|max:500'
            ]);

            // Find student by code
            $student = $this->studentRepository->findByCode($validated['student_code']);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found with code: ' . $validated['student_code']
                ], 404);
            }

            $date = $validated['date'] ? Carbon::parse($validated['date']) : Carbon::today();
            $result = null;

            switch ($validated['attendance_type']) {
                case 'check_in':
                    $checkInTime = $validated['check_in_time']
                        ? Carbon::parse($date->format('Y-m-d') . ' ' . $validated['check_in_time'])
                        : now();

                    $userId = Auth::check() ? Auth::user()->user_id : null;

                    $result = $this->attendanceRepository->checkIn($student->student_id, [
                        'check_in_time' => $checkInTime,
                        'device_id' => 'manual',
                        'recorded_by' => $userId,
                        'notes' => $validated['notes'] ?? null
                    ]);
                    break;

                case 'check_out':
                    $checkOutTime = $validated['check_out_time']
                        ? Carbon::parse($date->format('Y-m-d') . ' ' . $validated['check_out_time'])
                        : now();

                    $result = $this->attendanceRepository->checkOut($student->student_id, [
                        'check_out_time' => $checkOutTime,
                        'notes' => $validated['notes'] ?? null
                    ]);
                    break;

                case 'absent':
                    $userId = Auth::check() ? Auth::user()->user_id : null;

                    $result = $this->attendanceRepository->markAbsent($student->student_id, $date, [
                        'recorded_by' => $userId,
                        'remarks' => $validated['notes'] ?? 'Manually marked absent'
                    ]);
                    break;
            }

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attendance recorded successfully',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Manual attendance error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error recording attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search student by code (for manual entry)
     */
    public function searchStudent(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Student code is required'
            ], 400);
        }

        $student = $this->studentRepository->findByCode($code);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        // Get today's attendance status
        $todayAttendance = $this->attendanceRepository->getTodayAttendance($student->student_id);

        return response()->json([
            'success' => true,
            'data' => [
                'student' => [
                    'student_id' => $student->student_id,
                    'student_code' => $student->student_code,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'grade_level' => $student->grade_level,
                    'class_name' => $student->schoolClass->class_name ?? 'N/A'
                ],
                'today_attendance' => $todayAttendance ? [
                    'status' => $todayAttendance->status,
                    'check_in_time' => $todayAttendance->check_in_time?->format('H:i:s'),
                    'check_out_time' => $todayAttendance->check_out_time?->format('H:i:s'),
                    'is_late' => $todayAttendance->is_late
                ] : null
            ]
        ]);
    }

    /**
     * NFC check-in/check-out endpoint
     */
    public function nfcScan(Request $request)
    {
        try {
            // Read NFC tag
            $result = $this->arduinoService->readNFCTag();

            if (!$result['success'] || !$result['data']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }

            $studentCode = $result['data']['student_code'];

            // Find student
            $student = $this->studentRepository->findByCode($studentCode);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found in database'
                ], 404);
            }

            // Check if already checked in today
            $todayAttendance = $this->attendanceRepository->getTodayAttendance($student->student_id);

            if (!$todayAttendance || $todayAttendance->status === 'absent') {
                // Check in
                $attendance = $this->attendanceRepository->checkIn($student->student_id, [
                    'check_in_time' => now(),
                    'device_id' => 'nfc',
                    'nfc_tag_id' => $studentCode
                ]);

                return response()->json([
                    'success' => true,
                    'action' => 'check_in',
                    'message' => 'Student checked in successfully',
                    'data' => [
                        'student' => $student->first_name . ' ' . $student->last_name,
                        'time' => $attendance->check_in_time->format('H:i:s'),
                        'is_late' => $attendance->is_late
                    ]
                ]);
            } elseif ($todayAttendance->status === 'present' && !$todayAttendance->check_out_time) {
                // Check out
                $attendance = $this->attendanceRepository->checkOut($student->student_id, [
                    'check_out_time' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'action' => 'check_out',
                    'message' => 'Student checked out successfully',
                    'data' => [
                        'student' => $student->first_name . ' ' . $student->last_name,
                        'check_in' => $attendance->check_in_time->format('H:i:s'),
                        'check_out' => $attendance->check_out_time->format('H:i:s'),
                        'duration' => $attendance->duration
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Student already checked out today'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('NFC scan error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing NFC scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics
     */
    public function statistics(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $stats = $this->attendanceRepository->getStatistics(Carbon::parse($date));

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Generate attendance report
     */
    public function report(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:school_classes,class_id',
            'status' => 'nullable|in:present,absent,late,excused'
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $validated['status'] ?? null,
            'class_id' => $validated['class_id'] ?? null
        ];

        $attendances = $this->attendanceRepository->getReport($filters);

        return view('admin.pages.management.attendance.report', compact(
            'attendances',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get student attendance percentage
     */
    public function studentPercentage($studentId, Request $request)
    {
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : Carbon::now()->subDays(30);

        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))
            : Carbon::today();

        $percentage = $this->attendanceRepository->getStudentAttendancePercentage(
            $studentId,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => [
                'percentage' => $percentage,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Device management index page
     */
    public function devicesIndex()
    {
        return view('admin.pages.management.attendance.devices');
    }

    /**
     * Get list of registered devices
     */
    public function devicesList()
    {
        $devices = [];
        $cacheKeys = Cache::get('attendance_devices', []);

        foreach ($cacheKeys as $deviceId) {
            $deviceKey = "device_{$deviceId}";
            $deviceData = Cache::get($deviceKey);

            if ($deviceData) {
                $lastSeenKey = "device_{$deviceId}_last_seen";
                $lastSeen = Cache::get($lastSeenKey);

                $todayScans = $this->attendanceRepository->getAll()
                    ->where('device_id', $deviceId)
                    ->whereDate('created_at', Carbon::today())
                    ->count();

                $devices[] = [
                    'device_id' => $deviceId,
                    'device_name' => $deviceData['device_name'] ?? $deviceId,
                    'location' => $deviceData['location'] ?? null,
                    'last_seen' => $lastSeen,
                    'status' => $this->getDeviceStatus($lastSeen),
                    'today_scans' => $todayScans
                ];
            }
        }

        return response()->json([
            'success' => true,
            'devices' => $devices
        ]);
    }

    /**
     * Register a new device
     */
    public function devicesRegister(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50',
            'device_name' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100'
        ]);

        // Store device info
        $deviceKey = "device_{$validated['device_id']}";
        Cache::put($deviceKey, $validated, now()->addDays(365));

        // Add to devices list
        $devices = Cache::get('attendance_devices', []);
        if (!in_array($validated['device_id'], $devices)) {
            $devices[] = $validated['device_id'];
            Cache::put('attendance_devices', $devices, now()->addDays(365));
        }

        Log::info('Device registered via web interface', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'device' => $validated
        ]);
    }

    /**
     * Sync device (placeholder - actual sync happens via API)
     */
    public function devicesSync(Request $request)
    {
        $deviceId = $request->input('device_id');

        // This would typically trigger a sync request to the device
        // For now, just return status

        return response()->json([
            'success' => true,
            'message' => 'Sync completed. Upload pending records from device SD card via API endpoint.'
        ]);
    }

    /**
     * Remove a device
     */
    public function devicesRemove(Request $request)
    {
        $deviceId = $request->input('device_id');

        // Remove device data
        $deviceKey = "device_{$deviceId}";
        Cache::forget($deviceKey);
        Cache::forget("device_{$deviceId}_last_seen");
        Cache::forget("device_{$deviceId}_status");

        // Remove from devices list
        $devices = Cache::get('attendance_devices', []);
        $devices = array_diff($devices, [$deviceId]);
        Cache::put('attendance_devices', $devices, now()->addDays(365));

        Log::info('Device removed', ['device_id' => $deviceId]);

        return response()->json([
            'success' => true,
            'message' => 'Device removed successfully'
        ]);
    }

    /**
     * Get device status based on last seen time
     */
    private function getDeviceStatus($lastSeen)
    {
        if (!$lastSeen) {
            return 'offline';
        }

        $minutesAgo = Carbon::parse($lastSeen)->diffInMinutes(now());

        if ($minutesAgo < 2) {
            return 'online';
        } elseif ($minutesAgo < 10) {
            return 'idle';
        } else {
            return 'offline';
        }
    }
}
