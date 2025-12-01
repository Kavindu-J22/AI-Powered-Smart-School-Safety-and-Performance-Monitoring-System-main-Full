<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $fillable = [
        'class_code',
        'class_name',
        'grade_level',
        'academic_year',
        'section',
        'class_teacher_id',
        'room_number',
        'capacity',
        'description',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'academic_year' => 'string',
    ];

    /**
     * Get the class teacher for this class
     */
    public function classTeacher()
    {
        return $this->belongsTo(Teacher::class, 'class_teacher_id', 'teacher_id');
    }

    /**
     * Get students in this class
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'id');
    }

    /**
     * Get subjects taught in this class
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withTimestamps();
    }

    /**
     * Get timetables for this class
     */
    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'school_class_id');
    }

    /**
     * Get full class name with section
     */
    public function getFullNameAttribute()
    {
        return $this->class_name . ($this->section ? ' - ' . $this->section : '');
    }
}
