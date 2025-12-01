<?php

namespace App\Repositories\Interfaces\Admin\Management;

interface TeacherRepositoryInterface
{
    /**
     * Get all teachers
     */
    public function getAll();

    /**
     * Get teacher by ID
     */
    public function getById($id);

    /**
     * Create new teacher
     */
    public function create(array $data);

    /**
     * Update teacher
     */
    public function update($id, array $data);

    /**
     * Delete teacher
     */
    public function delete($id);

    /**
     * Get active teachers
     */
    public function getActive();

    /**
     * Get class teachers
     */
    public function getClassTeachers();

    /**
     * Get available teachers for class assignment
     */
    public function getAvailableClassTeachers($excludeClassId = null);

    /**
     * Get teachers by subject
     */
    public function getBySubject($subjectId);

    /**
     * Get teacher with relationships
     */
    public function getWithRelations($id);

    /**
     * Assign subjects to teacher
     */
    public function assignSubjects($teacherId, array $subjectIds);

    /**
     * Generate teacher code
     */
    public function generateTeacherCode();
}
