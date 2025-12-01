<?php

namespace App\Repositories\Interfaces\Admin\Management;

interface SchoolClassRepositoryInterface
{
    /**
     * Get all classes
     */
    public function getAll();

    /**
     * Get class by ID
     */
    public function getById($id);

    /**
     * Create new class
     */
    public function create(array $data);

    /**
     * Update class
     */
    public function update($id, array $data);

    /**
     * Delete class
     */
    public function delete($id);

    /**
     * Get classes by grade level
     */
    public function getByGrade($grade);

    /**
     * Get classes by academic year
     */
    public function getByAcademicYear($year);

    /**
     * Get class with relationships
     */
    public function getWithRelations($id);

    /**
     * Assign subjects to class
     */
    public function assignSubjects($classId, array $subjectIds);

    /**
     * Assign teacher to class
     */
    public function assignTeacher($classId, $teacherId);

    /**
     * Generate class code
     */
    public function generateClassCode();
}
