<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPerformance extends Model
{
    use HasFactory;

    protected $table = 'student_performance';
    protected $primaryKey = 'performance_id';

    protected $fillable = [
        'student_id',
        'subject_id',
        'grade_level',
        'academic_year',
        'term',
        'month',
        'total_homework_assigned',
        'homework_completed',
        'homework_on_time',
        'average_score',
        'highest_score',
        'lowest_score',
        'grade',
        'mcq_average',
        'short_answer_average',
        'descriptive_average',
        'trend',
        'strong_areas',
        'weak_areas',
        'recommendations',
    ];

    protected $casts = [
        'grade_level' => 'integer',
        'term' => 'integer',
        'month' => 'integer',
        'total_homework_assigned' => 'integer',
        'homework_completed' => 'integer',
        'homework_on_time' => 'integer',
        'average_score' => 'decimal:2',
        'highest_score' => 'decimal:2',
        'lowest_score' => 'decimal:2',
        'mcq_average' => 'decimal:2',
        'short_answer_average' => 'decimal:2',
        'descriptive_average' => 'decimal:2',
        'strong_areas' => 'array',
        'weak_areas' => 'array',
        'recommendations' => 'array',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
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

    public function scopeByAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeByMonth($query, int $month)
    {
        return $query->where('month', $month);
    }

    // Helper methods
    public function getCompletionRate(): float
    {
        if ($this->total_homework_assigned == 0) return 0;
        return round(($this->homework_completed / $this->total_homework_assigned) * 100, 1);
    }

    public function getOnTimeRate(): float
    {
        if ($this->homework_completed == 0) return 0;
        return round(($this->homework_on_time / $this->homework_completed) * 100, 1);
    }

    public function getTrendIcon(): string
    {
        return match($this->trend) {
            'improving' => 'trending_up',
            'stable' => 'trending_flat',
            'declining' => 'trending_down',
            'needs_attention' => 'warning',
            default => 'help'
        };
    }

    public function getTrendColor(): string
    {
        return match($this->trend) {
            'improving' => 'success',
            'stable' => 'info',
            'declining' => 'warning',
            'needs_attention' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Update performance metrics from submissions
     */
    public static function updateFromSubmissions(int $studentId, int $subjectId, string $academicYear, int $month): self
    {
        $performance = self::firstOrNew([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_year' => $academicYear,
            'month' => $month,
        ]);

        // Get submissions for this period
        $submissions = HomeworkSubmission::where('student_id', $studentId)
            ->whereHas('homework', function ($q) use ($subjectId, $month) {
                $q->where('subject_id', $subjectId)
                  ->whereMonth('due_date', $month);
            })
            ->where('status', 'graded')
            ->get();

        if ($submissions->isEmpty()) {
            return $performance;
        }

        $performance->total_homework_assigned = $submissions->count();
        $performance->homework_completed = $submissions->where('status', 'graded')->count();
        $performance->homework_on_time = $submissions->where('is_late', false)->count();
        $performance->average_score = $submissions->avg('percentage');
        $performance->highest_score = $submissions->max('percentage');
        $performance->lowest_score = $submissions->min('percentage');
        $performance->grade = HomeworkSubmission::calculateGrade($performance->average_score);

        $performance->save();
        return $performance;
    }
}

