<?php

namespace App\Repositories\Admin\Management;

use App\Models\Attendance;
use App\Models\Student;
use App\Repositories\Interfaces\Admin\Management\AttendanceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    protected $model;

    public function __construct(Attendance $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->with(['student', 'recorder'])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();
    }

    public function getById($id)
    {
        return $this->model->with(['student', 'recorder'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $attendance = $this->getById($id);
        if ($attendance) {
            $attendance->update($data);
            return $attendance;
        }
        return null;
    }

    public function delete($id)
    {
        $attendance = $this->getById($id);
        if ($attendance) {
            return $attendance->delete();
        }
        return false;
    }

    public function getByDate($date)
    {
        return $this->model->with(['student', 'recorder'])
            ->whereDate('attendance_date', $date)
            ->orderBy('check_in_time', 'asc')
            ->get();
    }

    public function getByStudent($studentId)
    {
        return $this->model->with(['student', 'recorder'])
            ->where('student_id', $studentId)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function getToday()
    {
        return $this->model->with(['student', 'recorder'])
            ->whereDate('attendance_date', Carbon::today())
            ->orderBy('check_in_time', 'asc')
            ->get();
    }

    public function getByStatus($status)
    {
        return $this->model->with(['student', 'recorder'])
            ->where('status', $status)
            ->whereDate('attendance_date', Carbon::today())
            ->orderBy('check_in_time', 'asc')
            ->get();
    }

    public function checkIn($studentId, array $data = [])
    {
        $today = Carbon::today();

        // Check if already checked in today
        $existing = $this->model->where('student_id', $studentId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing) {
            // Update check-in time if needed
            if (!$existing->check_in_time) {
                $existing->update([
                    'check_in_time' => Carbon::now(),
                    'status' => 'present',
                    ...$data
                ]);
                return $existing;
            }
            return $existing; // Already checked in
        }

        // Create new attendance record
        return $this->create([
            'student_id' => $studentId,
            'attendance_date' => $today,
            'check_in_time' => Carbon::now(),
            'status' => 'present',
            'is_auto_recorded' => true,
            ...$data
        ]);
    }

    public function checkOut($studentId, array $data = [])
    {
        $today = Carbon::today();

        $attendance = $this->model->where('student_id', $studentId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($attendance) {
            $attendance->update([
                'check_out_time' => Carbon::now(),
                ...$data
            ]);
            return $attendance;
        }

        return null; // No check-in record found
    }

    public function getStatistics($startDate = null, $endDate = null)
    {
        $query = $this->model->query();

        if ($startDate) {
            $query->whereDate('attendance_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('attendance_date', '<=', $endDate);
        }

        $total = $query->count();

        return [
            'total' => $total,
            'present' => $query->clone()->where('status', 'present')->count(),
            'late' => $query->clone()->where('status', 'late')->count(),
            'absent' => $query->clone()->where('status', 'absent')->count(),
            'excused' => $query->clone()->where('status', 'excused')->count(),
            'present_percentage' => $total > 0 ? round(($query->clone()->whereIn('status', ['present', 'late'])->count() / $total) * 100, 2) : 0,
        ];
    }

    public function getStudentAttendancePercentage($studentId, $startDate = null, $endDate = null)
    {
        $query = $this->model->where('student_id', $studentId);

        if ($startDate) {
            $query->whereDate('attendance_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('attendance_date', '<=', $endDate);
        }

        $total = $query->count();
        $present = $query->clone()->whereIn('status', ['present', 'late'])->count();

        return $total > 0 ? round(($present / $total) * 100, 2) : 0;
    }

    public function markAbsent($studentId, $date, array $data = [])
    {
        $existing = $this->model->where('student_id', $studentId)
            ->whereDate('attendance_date', $date)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => 'absent',
                ...$data
            ]);
            return $existing;
        }

        return $this->create([
            'student_id' => $studentId,
            'attendance_date' => $date,
            'status' => 'absent',
            'is_auto_recorded' => false,
            ...$data
        ]);
    }

    public function getReport($filters = [])
    {
        $query = $this->model->with(['student', 'recorder']);

        // Apply filters
        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('attendance_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('attendance_date', '<=', $filters['end_date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['class_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        return $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'asc')
            ->get();
    }

    /**
     * Auto-mark absent students for a given date
     */
    public function autoMarkAbsent($date = null)
    {
        $date = $date ?? Carbon::today();

        // Get all active students
        $activeStudents = Student::where('is_active', true)->pluck('student_id');

        // Get students who have attendance for the date
        $presentStudents = $this->model->whereDate('attendance_date', $date)
            ->pluck('student_id');

        // Find students who are absent (not in present list)
        $absentStudents = $activeStudents->diff($presentStudents);

        // Mark them as absent
        foreach ($absentStudents as $studentId) {
            $this->markAbsent($studentId, $date, [
                'is_auto_recorded' => true,
                'remarks' => 'Auto-marked absent - no check-in recorded'
            ]);
        }

        return $absentStudents->count();
    }

    /**
     * Get today's attendance for a specific student
     */
    public function getTodayAttendance($studentId)
    {
        return $this->model->where('student_id', $studentId)
            ->whereDate('attendance_date', Carbon::today())
            ->first();
    }

    /**
     * Get attendance by specific date for a student
     */
    public function getAttendanceByDate($studentId, $date)
    {
        return $this->model->where('student_id', $studentId)
            ->whereDate('attendance_date', $date)
            ->first();
    }

    /**
     * Get student attendance history within date range
     */
    public function getStudentAttendance($studentId, $startDate, $endDate)
    {
        return $this->model->with(['student', 'recorder'])
            ->where('student_id', $studentId)
            ->whereDate('attendance_date', '>=', $startDate)
            ->whereDate('attendance_date', '<=', $endDate)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }
}
