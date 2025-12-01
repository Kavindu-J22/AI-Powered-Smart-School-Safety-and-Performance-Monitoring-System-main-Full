<?php

namespace App\Repositories\Admin\Management;

use App\Models\Student;
use App\Models\Subject;
use App\Repositories\Interfaces\Admin\Management\StudentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentRepository implements StudentRepositoryInterface
{
    protected Student $model;

    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    /**
     * Get all students
     */
    public function getAll(): Collection
    {
        return $this->model->with(['user', 'schoolClass', 'parents'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get student by ID
     */
    public function getById($id): ?Student
    {
        return $this->model->with(['user', 'schoolClass', 'parents', 'subjects'])
            ->where('student_id', $id)
            ->first();
    }

    /**
     * Create new student
     */
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['student_code'])) {
                $data['student_code'] = $this->generateStudentCode();
            }

            $student = $this->model->create($data);

            // Assign subjects based on grade if provided
            if (isset($data['grade_level']) && isset($data['subjects'])) {
                $this->assignSubjects($student->student_id, $data['subjects'], $data['grade_level']);
            }

            return $student;
        });
    }

    /**
     * Update student
     */
    public function update($id, array $data): Student|bool
    {
        return DB::transaction(function () use ($id, $data) {
            $student = $this->model->where('student_id', $id)->first();

            if (! $student) {
                return false;
            }

            $oldGrade = $student->grade_level;
            $newGrade = $data['grade_level'] ?? $oldGrade;

            $student->update($data);

            // If grade changed, update subjects
            if ($oldGrade != $newGrade && isset($data['subjects'])) {
                $this->assignSubjects($id, $data['subjects'], $newGrade);
            }

            return $student;
        });
    }

    /**
     * Delete student
     */
    public function delete($id): bool
    {
        return $this->model->where('student_id', $id)->delete();
    }

    /**
     * Get students by grade level
     */
    public function getByGrade(int $grade): Collection
    {
        return $this->model->with(['user', 'schoolClass'])
            ->where('grade_level', $grade)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get students by class
     */
    public function getByClass(int $classId): Collection
    {
        return $this->model->with(['user', 'schoolClass'])
            ->where('class_id', $classId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update student grade and subjects
     */
    public function updateGrade($id, int $newGrade): Student|bool
    {
        return DB::transaction(function () use ($id, $newGrade) {
            $student = $this->model->where('student_id', $id)->first();

            if (! $student) {
                return false;
            }

            // Update grade
            $student->update(['grade_level' => $newGrade]);

            // Get subjects for new grade
            $gradeSubjects = Subject::where('grade_level', $newGrade)->get();

            // Remove old subjects and assign new ones
            $student->subjects()->detach();

            if ($gradeSubjects->isNotEmpty()) {
                $this->assignSubjects($id, $gradeSubjects->pluck('id')->toArray(), $newGrade);
            }

            return $student;
        });
    }

    /**
     * Get student with relationships
     */
    public function getWithRelations($id): ?Student
    {
        return $this->model->with(['user', 'schoolClass', 'parents', 'subjects'])
            ->where('student_id', $id)
            ->first();
    }

    /**
     * Assign subjects to student
     */
    public function assignSubjects($studentId, array $subjectIds, int $grade): bool
    {
        $student = $this->model->where('student_id', $studentId)->first();

        if (! $student) {
            return false;
        }

        $syncData = [];
        foreach ($subjectIds as $subjectId) {
            $syncData[$subjectId] = [
                'enrollment_date' => now(),
                'grade' => $grade,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $student->subjects()->sync($syncData);

        return true;
    }

    /**
     * Generate student code
     */
    public function generateStudentCode(): string
    {
        return Student::generateStudentCode();
    }

    /**
     * Find student by student code
     */
    public function findByCode(string $studentCode): ?Student
    {
        return $this->model->with(['user', 'schoolClass', 'parents'])
            ->where('student_code', $studentCode)
            ->first();
    }
}
