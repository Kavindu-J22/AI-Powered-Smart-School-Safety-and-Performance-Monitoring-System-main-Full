<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'teacher_id';

    protected $fillable = [
        'user_id',
        'teacher_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'nationality',
        'qualification',
        'specialization',
        'experience_years',
        'joining_date',
        'employee_id',
        'photo_path',
        'is_active',
        'teaching_level',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'home_phone',
        'mobile_phone',
        'email',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'experience_years' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'class_teacher_id', 'teacher_id');
    }

    public function assignedClass()
    {
        return $this->hasOne(SchoolClass::class, 'class_teacher_id', 'teacher_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
            ->withTimestamps();
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    public function getFullAddressAttribute()
    {
        return trim($this->address_line1 . ' ' . $this->address_line2 . ', ' . $this->city . ', ' . $this->state . ' ' . $this->postal_code . ', ' . $this->country);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTeachingLevel($query, $teachingLevel)
    {
        return $query->where('teaching_level', $teachingLevel);
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    // Static methods
    public static function generateTeacherCode()
    {
        $lastTeacher = self::orderBy('teacher_id', 'desc')->first();
        $sequence = $lastTeacher ? (int) substr($lastTeacher->teacher_code, 3) + 1 : 1;

        return 'te-' . str_pad($sequence, 8, '0', STR_PAD_LEFT);
    }
}
