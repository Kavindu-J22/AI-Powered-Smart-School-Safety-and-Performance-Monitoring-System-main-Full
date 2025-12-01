<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'label',
        'is_break',
        'slot_number',
        'slot_type',
        'day_of_week',
        'period_number',
        'description',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_break' => 'boolean',
        'slot_number' => 'integer',
        'day_of_week' => 'integer',
        'period_number' => 'integer',
    ];

    /**
     * Get timetables for this time slot
     */
    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'time_slot_id');
    }

    /**
     * Get formatted time slot display
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    /**
     * Get slot name (alias for label)
     */
    public function getSlotNameAttribute()
    {
        return $this->label;
    }

    /**
     * Get slot type from database (removed accessor to use actual column value)
     */

    /**
     * Check if time slot is during regular school hours
     */
    public function getIsRegularHoursAttribute()
    {
        $regularStart = '08:00';
        $regularEnd = '13:30';

        return $this->start_time->format('H:i') >= $regularStart &&
            $this->end_time->format('H:i') <= $regularEnd;
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutesAttribute()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Check if this is a preset fixed schedule slot (8:00 AM - 1:30 PM)
     */
    public function getIsPresetSlotAttribute()
    {
        return $this->start_time->format('H:i') >= '08:00' &&
            $this->end_time->format('H:i') <= '13:30' &&
            in_array($this->slot_type, ['regular', 'break']);
    }

    /**
     * Check if this slot can be deleted by admin
     */
    public function getCanBeDeletedAttribute()
    {
        return $this->slot_type === 'additional' &&
            $this->start_time->format('H:i') >= '13:30';
    }

    /**
     * Check if this slot can be edited by admin
     */
    public function getCanBeEditedAttribute()
    {
        // Only additional slots after 1:30 PM can be edited
        return $this->slot_type === 'additional' &&
            $this->start_time->format('H:i') >= '13:30';
    }

    /**
     * Scope for preset fixed schedule slots only
     */
    public function scopePresetOnly($query)
    {
        return $query->where(function ($q) {
            $q->where('slot_type', 'regular')
                ->orWhere('slot_type', 'break');
        })->whereTime('start_time', '>=', '08:00')
            ->whereTime('end_time', '<=', '13:30');
    }

    /**
     * Scope for additional slots only (after 1:30 PM)
     */
    public function scopeAdditionalOnly($query)
    {
        return $query->where('slot_type', 'additional')
            ->whereTime('start_time', '>=', '13:30');
    }
}
