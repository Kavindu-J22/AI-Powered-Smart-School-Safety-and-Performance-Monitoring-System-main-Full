<?php

namespace App\Models;

use App\Enums\Grade;
use App\Enums\SubjectCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_code',
        'subject_name',
        'grade_level',
        'description',
        'credits',
        'type',
        'category',
        'is_required',
        'stream',
        'status',
    ];

    protected $casts = [
        'credits' => 'integer',
        'is_required' => 'boolean',
    ];

    /**
     * Get classes that use this subject
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'class_id')
            ->withTimestamps();
    }

    /**
     * Get teachers who teach this subject
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject', 'subject_id', 'teacher_id')
            ->withTimestamps();
    }

    /**
     * Get students enrolled in this subject
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_subject', 'subject_id', 'student_id')
            ->withPivot('enrollment_date', 'grade')
            ->withTimestamps();
    }

    /**
     * Scope to filter subjects by grade level
     */
    public function scopeByGradeLevel(Builder $query, string $gradeLevel): Builder
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope to filter active subjects
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Get subjects for a specific grade using enum
     */
    public static function getSubjectsForGrade(Grade $grade): \Illuminate\Database\Eloquent\Collection
    {
        $gradeLevel = $grade->getGradeLevel();

        return self::where('grade_level', $gradeLevel)
            ->where('status', 'active')
            ->orderBy('subject_name')
            ->get();
    }

    /**
     * Get subjects for a specific grade value
     */
    public static function getSubjectsForGradeValue(int $gradeValue): \Illuminate\Database\Eloquent\Collection
    {
        $grade = Grade::from($gradeValue);
        return self::getSubjectsForGrade($grade);
    }

    /**
     * Get subjects grouped by category for a specific grade
     */
    public static function getSubjectsGroupedByCategory(Grade $grade): array
    {
        $gradeLevel = $grade->getGradeLevel();
        $subjects = self::where('grade_level', $gradeLevel)
            ->where('status', 'active')
            ->orderBy('category')
            ->orderBy('subject_name')
            ->get();

        return $subjects->groupBy('category')->toArray();
    }

    /**
     * Get required subjects for a specific grade (auto-assigned)
     */
    public static function getRequiredSubjects(Grade $grade): \Illuminate\Database\Eloquent\Collection
    {
        $gradeLevel = $grade->getGradeLevel();

        return self::where('grade_level', $gradeLevel)
            ->where('status', 'active')
            ->where('is_required', true)
            ->where('category', 'Core')
            ->orderBy('subject_name')
            ->get();
    }

    /**
     * Get choice subjects (First Language, Religion, etc.) that require selection
     */
    public static function getChoiceSubjects(Grade $grade, string $category): \Illuminate\Database\Eloquent\Collection
    {
        $gradeLevel = $grade->getGradeLevel();

        return self::where('grade_level', $gradeLevel)
            ->where('status', 'active')
            ->where('category', $category)
            ->orderBy('subject_name')
            ->get();
    }

    /**
     * Get elective subjects for secondary education
     */
    public static function getElectiveSubjects(Grade $grade): \Illuminate\Database\Eloquent\Collection
    {
        $gradeLevel = $grade->getGradeLevel();

        return self::where('grade_level', $gradeLevel)
            ->where('status', 'active')
            ->where('category', 'Elective')
            ->orderBy('subject_name')
            ->get();
    }

    /**
     * Get stream subjects for Advanced Level
     */
    public static function getStreamSubjects(Grade $grade, string $stream): \Illuminate\Database\Eloquent\Collection
    {
        $gradeLevel = $grade->getGradeLevel();

        return self::where('grade_level', $gradeLevel)
            ->where('status', 'active')
            ->where('stream', $stream)
            ->orderBy('subject_name')
            ->get();
    }

    /**
     * Get all subjects for a grade with detailed categorization
     */
    public static function getSubjectsWithRules(int $gradeValue): array
    {
        $grade = Grade::from($gradeValue);
        $gradeLevel = $grade->getGradeLevel();

        $data = [
            'grade' => $gradeValue,
            'grade_level' => $gradeLevel,
            'education_level' => $grade->getEducationLevel(),
            'subjects' => [],
            'rules' => [],
        ];

        // Primary Education (Grades 1-5)
        if ($grade->isPrimary()) {
            $data['subjects'] = [
                'first_language' => self::getChoiceSubjects($grade, 'First Language'),
                'core' => self::getRequiredSubjects($grade),
                'religion' => self::getChoiceSubjects($grade, 'Religion'),
                'aesthetic' => self::getChoiceSubjects($grade, 'Aesthetic Studies'),
            ];

            $data['rules'] = [
                'first_language_required' => 1,
                'religion_required' => 1,
                'aesthetic_required' => 1,
                'core_auto_assigned' => true,
            ];
        }
        // Secondary Education (Grades 6-11)
        elseif ($grade->isSecondary()) {
            $data['subjects'] = [
                'first_language' => self::getChoiceSubjects($grade, 'First Language'),
                'core' => self::getRequiredSubjects($grade),
                'religion' => self::getChoiceSubjects($grade, 'Religion'),
                'elective' => self::getElectiveSubjects($grade),
            ];

            $data['rules'] = [
                'first_language_required' => 1,
                'religion_required' => 1,
                'elective_required' => 3,
                'core_auto_assigned' => true,
            ];
        }
        // Advanced Level (Grades 12-13)
        elseif ($grade->isAdvancedLevel()) {
            // Get all streams
            $streams = ['Arts', 'Commerce', 'Science', 'Technology'];
            $streamSubjects = [];

            foreach ($streams as $stream) {
                $streamSubjects[$stream] = self::getStreamSubjects($grade, $stream);
            }

            $data['subjects'] = [
                'streams' => $streamSubjects,
            ];

            $data['rules'] = [
                'stream_required' => 1,
                'stream_subjects_required' => 3,
            ];
        }

        return $data;
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter required subjects
     */
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope to filter by stream
     */
    public function scopeByStream(Builder $query, string $stream): Builder
    {
        return $query->where('stream', $stream);
    }
}
