<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'student_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'nfc_tag_id',
        'check_in_location',
        'check_out_location',
        'device_id',
        'temperature',
        'remarks',
        'recorded_by',
        'is_auto_recorded',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'temperature' => 'decimal:1',
        'is_auto_recorded' => 'boolean',
    ];

    /**
     * Get the student that owns the attendance record
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the user who recorded the attendance
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope to get attendance for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    /**
     * Scope to get attendance for a specific student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get attendance by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', Carbon::today());
    }

    /**
     * Scope to get this week's attendance
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('attendance_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ]);
    }

    /**
     * Scope to get this month's attendance
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('attendance_date', Carbon::now()->month)
            ->whereYear('attendance_date', Carbon::now()->year);
    }

    /**
     * Check if student is late
     */
    public function isLate($schoolStartTime = '08:00:00')
    {
        if (!$this->check_in_time) {
            return false;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $startTime = Carbon::parse($schoolStartTime);

        return $checkIn->gt($startTime);
    }

    /**
     * Calculate duration of stay (if both check-in and check-out exist)
     */
    public function getDurationAttribute()
    {
        if ($this->check_in_time && $this->check_out_time) {
            $checkIn = Carbon::parse($this->check_in_time);
            $checkOut = Carbon::parse($this->check_out_time);
            return $checkIn->diff($checkOut)->format('%H:%I:%S');
        }
        return null;
    }

    /**
     * Get attendance status badge class
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'present' => 'bg-success',
            'late' => 'bg-warning',
            'absent' => 'bg-danger',
            'excused' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * Mark attendance as late if check-in time is after school start time
     */
    public function checkAndUpdateLateStatus($schoolStartTime = '08:00:00')
    {
        if ($this->status === 'present' && $this->isLate($schoolStartTime)) {
            $this->status = 'late';
            $this->save();
        }
    }
}
