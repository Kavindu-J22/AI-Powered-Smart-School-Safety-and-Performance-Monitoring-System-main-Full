<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Homework extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'homework';
    protected $primaryKey = 'homework_id';

    public function getRouteKeyName(): string
    {
        return 'homework_id';
    }

    protected $fillable = [
        'lesson_id',
        'subject_id',
        'class_id',
        'assigned_by',
        'grade_level',
        'title',
        'description',
        'questions',
        'total_marks',
        'assigned_date',
        'due_date',
        'status',
        'week_number',
        'academic_year',
    ];

    protected $casts = [
        'questions' => 'array',
        'assigned_date' => 'date',
        'due_date' => 'date',
        'grade_level' => 'integer',
        'total_marks' => 'integer',
        'week_number' => 'integer',
    ];

    // Relationships
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'lesson_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'assigned_by', 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class, 'homework_id', 'homework_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByGrade($query, int $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function scopeByClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
            ->where('status', 'active');
    }

    // Helper methods
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->status === 'active';
    }

    public function getDaysUntilDue(): int
    {
        return Carbon::now()->diffInDays($this->due_date, false);
    }

    public function getQuestionCount(): int
    {
        return is_array($this->questions) ? count($this->questions) : 0;
    }

    public function getQuestionsByType(): array
    {
        $breakdown = ['MCQ' => 0, 'SHORT_ANSWER' => 0, 'DESCRIPTIVE' => 0];

        if (is_array($this->questions)) {
            foreach ($this->questions as $q) {
                $type = $q['question_type'] ?? 'MCQ';
                $breakdown[$type] = ($breakdown[$type] ?? 0) + 1;
            }
        }

        return $breakdown;
    }

    public function getSubmissionStats(): array
    {
        $total = $this->submissions()->count();
        $submitted = $this->submissions()->where('status', 'submitted')->orWhere('status', 'graded')->count();
        $graded = $this->submissions()->where('status', 'graded')->count();

        return [
            'total_assigned' => $total,
            'submitted' => $submitted,
            'graded' => $graded,
            'pending' => $total - $submitted,
            'submission_rate' => $total > 0 ? round(($submitted / $total) * 100, 1) : 0,
        ];
    }

    public static function getCurrentAcademicYear(): string
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($currentMonth >= 8) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
}
