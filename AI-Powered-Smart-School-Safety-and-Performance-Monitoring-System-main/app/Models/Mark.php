<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mark extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'mark_id';

    protected $fillable = [
        'student_id',
        'subject_id',
        'grade_level',
        'academic_year',
        'term',
        'marks',
        'total_marks',
        'percentage',
        'grade',
        'remarks',
        'entered_by',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'grade_level' => 'integer',
        'term' => 'integer',
    ];

    // Automatically calculate percentage and grade before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($mark) {
            // Calculate percentage
            if ($mark->marks !== null && $mark->total_marks > 0) {
                $mark->percentage = ($mark->marks / $mark->total_marks) * 100;

                // Calculate grade based on percentage
                $mark->grade = self::calculateGrade($mark->percentage);
            }
        });
    }

    /**
     * Calculate grade based on percentage
     */
    public static function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 85) return 'A';
        if ($percentage >= 80) return 'A-';
        if ($percentage >= 75) return 'B+';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 65) return 'B-';
        if ($percentage >= 60) return 'C+';
        if ($percentage >= 55) return 'C';
        if ($percentage >= 50) return 'C-';
        if ($percentage >= 45) return 'D+';
        if ($percentage >= 40) return 'D';
        return 'F';
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by', 'id');
    }

    // Scopes
    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByGrade($query, int $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function scopeByAcademicYear($query, string $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeByTerm($query, int $term)
    {
        return $query->where('term', $term);
    }

    // Static methods
    public static function getTerms(): array
    {
        return [
            1 => 'Term 1',
            2 => 'Term 2',
            3 => 'Term 3',
        ];
    }

    public static function getCurrentAcademicYear(): string
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Academic year starts in August (month 8)
        if ($currentMonth >= 8) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
}
