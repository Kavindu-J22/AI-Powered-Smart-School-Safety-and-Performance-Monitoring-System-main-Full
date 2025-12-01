<?php

namespace App\Repositories\Interfaces\Admin\Management;

interface SubjectRepositoryInterface
{
    /**
     * Get all subjects
     */
    public function getAll();

    /**
     * Get subject by ID
     */
    public function getById($id);

    /**
     * Create new subject
     */
    public function create(array $data);

    /**
     * Update subject
     */
    public function update($id, array $data);

    /**
     * Delete subject
     */
    public function delete($id);

    /**
     * Get subjects by grade level
     */
    public function getByGrade($grade);

    /**
     * Get subjects by type
     */
    public function getByType($type);

    /**
     * Get subject with relationships
     */
    public function getWithRelations($id);

    /**
     * Get subjects for class
     */
    public function getForClass($classId);

    /**
     * Get subjects for teacher
     */
    public function getForTeacher($teacherId);

    /**
     * Generate subject code
     */
    public function generateSubjectCode();

    /**
     * Check if subject code exists
     */
    public function existsByCode(string $code): bool;
}
