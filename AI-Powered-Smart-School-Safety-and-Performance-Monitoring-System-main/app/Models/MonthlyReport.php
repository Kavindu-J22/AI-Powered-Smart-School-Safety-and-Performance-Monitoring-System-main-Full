<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyReport extends Model
{
    use HasFactory;

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'student_id',
        'grade_level',
        'academic_year',
        'month',
        'year',
        'overall_average',
        'overall_grade',
        'class_rank',
        'total_students_in_class',
        'subject_performance',
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'total_homework_assigned',
        'homework_completed',
        'homework_on_time',
        'completion_rate',
        'status',
        'sent_to_parents_at',
        'parent_acknowledged_at',
        'report_file_path',
    ];

    protected $casts = [
        'grade_level' => 'integer',
        'month' => 'integer',
        'year' => 'integer',
        'class_rank' => 'integer',
        'total_students_in_class' => 'integer',
        'overall_average' => 'decimal:2',
        'total_homework_assigned' => 'integer',
        'homework_completed' => 'integer',
        'homework_on_time' => 'integer',
        'completion_rate' => 'decimal:2',
        'subject_performance' => 'array',
        'strengths' => 'array',
        'areas_for_improvement' => 'array',
        'recommendations' => 'array',
        'sent_to_parents_at' => 'datetime',
        'parent_acknowledged_at' => 'datetime',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    // Scopes
    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopePendingSend($query)
    {
        return $query->where('status', 'generated');
    }

    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent_to_parents', 'acknowledged']);
    }

    // Helper methods
    public function getMonthName(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function getReportPeriod(): string
    {
        return $this->getMonthName() . ' ' . $this->year;
    }

    public function isSentToParents(): bool
    {
        return in_array($this->status, ['sent_to_parents', 'acknowledged']);
    }

    public function isAcknowledged(): bool
    {
        return $this->status === 'acknowledged';
    }

    public function getGradeColor(): string
    {
        if (!$this->overall_grade) return 'secondary';
        
        $grade = strtoupper($this->overall_grade);
        if (str_starts_with($grade, 'A')) return 'success';
        if (str_starts_with($grade, 'B')) return 'info';
        if (str_starts_with($grade, 'C')) return 'warning';
        return 'danger';
    }

    public function getRankText(): string
    {
        if (!$this->class_rank || !$this->total_students_in_class) {
            return 'N/A';
        }
        return "{$this->class_rank} of {$this->total_students_in_class}";
    }

    /**
     * Generate monthly report for a student
     */
    public static function generateForStudent(int $studentId, int $year, int $month): self
    {
        $student = Student::find($studentId);
        if (!$student) {
            throw new \Exception("Student not found");
        }

        $report = self::firstOrNew([
            'student_id' => $studentId,
            'year' => $year,
            'month' => $month,
        ]);

        $report->grade_level = $student->grade_level;
        $report->academic_year = Homework::getCurrentAcademicYear();

        // Get performance data for each subject
        $performances = StudentPerformance::where('student_id', $studentId)
            ->where('month', $month)
            ->with('subject')
            ->get();

        $subjectPerformance = [];
        $totalAverage = 0;
        $subjectCount = 0;

        foreach ($performances as $perf) {
            $subjectPerformance[$perf->subject->subject_name ?? 'Unknown'] = [
                'average' => $perf->average_score,
                'grade' => $perf->grade,
                'trend' => $perf->trend,
                'completed' => $perf->homework_completed,
                'assigned' => $perf->total_homework_assigned,
            ];
            $totalAverage += $perf->average_score;
            $subjectCount++;
        }

        $report->subject_performance = $subjectPerformance;
        $report->overall_average = $subjectCount > 0 ? $totalAverage / $subjectCount : 0;
        $report->overall_grade = HomeworkSubmission::calculateGrade($report->overall_average);
        $report->status = 'generated';

        $report->save();
        return $report;
    }
}

