<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'lesson_id';

    public function getRouteKeyName(): string
    {
        return 'lesson_id';
    }

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'grade_level',
        'unit',
        'title',
        'content',
        'topics',
        'keywords',
        'learning_outcomes',
        'difficulty',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'topics' => 'array',
        'keywords' => 'array',
        'learning_outcomes' => 'array',
        'grade_level' => 'integer',
        'duration_minutes' => 'integer',
    ];

    // Relationships
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class, 'lesson_id', 'lesson_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByGrade($query, int $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function scopeByTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Helper methods
    public function getFormattedDuration(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes} minutes";
    }

    public function getTopicsString(): string
    {
        return is_array($this->topics) ? implode(', ', $this->topics) : '';
    }

    /**
     * Get lesson data formatted for AI processing
     */
    public function getAIFormatted(): array
    {
        return [
            'lesson_id' => $this->lesson_id,
            'subject' => $this->subject->subject_name ?? '',
            'grade' => $this->grade_level,
            'unit' => $this->unit,
            'title' => $this->title,
            'content' => $this->content,
            'topics' => $this->topics ?? [],
            'keywords' => $this->keywords ?? [],
            'learning_outcomes' => $this->learning_outcomes ?? [],
            'difficulty' => $this->difficulty,
        ];
    }
}
