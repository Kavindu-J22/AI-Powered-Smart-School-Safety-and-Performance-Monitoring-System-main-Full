<?php

namespace App\Repositories\Interfaces\Admin\Management;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface
{
    /**
     * Get all students
     */
    public function getAll(): Collection;

    /**
     * Get student by ID
     */
    public function getById($id): ?Student;

    /**
     * Create new student
     */
    public function create(array $data): Student;

    /**
     * Update student
     */
    public function update($id, array $data): Student|bool;

    /**
     * Delete student
     */
    public function delete($id): bool;

    /**
     * Get students by grade level
     */
    public function getByGrade(int $grade): Collection;

    /**
     * Get students by class
     */
    public function getByClass(int $classId): Collection;

    /**
     * Update student grade and subjects
     */
    public function updateGrade($id, int $newGrade): Student|bool;

    /**
     * Get student with relationships
     */
    public function getWithRelations($id): ?Student;

    /**
     * Assign subjects to student
     */
    public function assignSubjects($studentId, array $subjectIds, int $grade): bool;

    /**
     * Generate student code
     */
    public function generateStudentCode(): string;

    /**
     * Find student by student code
     */
    public function findByCode(string $studentCode): ?Student;
}
