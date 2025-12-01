<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeworkSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'submission_id';

    protected $fillable = [
        'homework_id',
        'student_id',
        'answers',
        'evaluation_results',
        'marks_obtained',
        'percentage',
        'grade',
        'feedback',
        'started_at',
        'submitted_at',
        'graded_at',
        'status',
        'is_late',
    ];

    protected $casts = [
        'answers' => 'array',
        'evaluation_results' => 'array',
        'marks_obtained' => 'decimal:2',
        'percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    // Relationships
    public function homework(): BelongsTo
    {
        return $this->belongsTo(Homework::class, 'homework_id', 'homework_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'graded']);
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['assigned', 'in_progress']);
    }

    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    // Helper methods
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded']);
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function getGradeColor(): string
    {
        if (!$this->grade) return 'secondary';
        
        $grade = strtoupper($this->grade);
        if (str_starts_with($grade, 'A')) return 'success';
        if (str_starts_with($grade, 'B')) return 'info';
        if (str_starts_with($grade, 'C')) return 'warning';
        return 'danger';
    }

    public function getQuestionResults(): array
    {
        $results = [];
        $evaluations = $this->evaluation_results ?? [];
        
        foreach ($evaluations as $eval) {
            $results[] = [
                'question_idx' => $eval['question_idx'] ?? 0,
                'is_correct' => $eval['evaluation']['is_correct'] ?? false,
                'marks_obtained' => $eval['evaluation']['marks_obtained'] ?? 0,
                'max_marks' => $eval['evaluation']['max_marks'] ?? 0,
                'feedback' => $eval['evaluation']['feedback'] ?? '',
            ];
        }
        
        return $results;
    }

    /**
     * Calculate grade from percentage
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

    /**
     * Check if submission is late based on homework due date
     */
    public function checkAndUpdateLateStatus(): void
    {
        if ($this->submitted_at && $this->homework) {
            $this->is_late = $this->submitted_at->isAfter($this->homework->due_date->endOfDay());
            $this->save();
        }
    }
}

