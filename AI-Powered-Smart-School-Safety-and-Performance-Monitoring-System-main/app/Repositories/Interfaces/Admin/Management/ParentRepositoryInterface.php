<?php

namespace App\Repositories\Interfaces\Admin\Management;

interface ParentRepositoryInterface
{
    /**
     * Get all parents
     */
    public function getAll();

    /**
     * Get parent by ID
     */
    public function getById($id);

    /**
     * Create new parent
     */
    public function create(array $data);

    /**
     * Update parent
     */
    public function update($id, array $data);

    /**
     * Delete parent
     */
    public function delete($id);

    /**
     * Get active parents
     */
    public function getActive();

    /**
     * Get emergency contacts
     */
    public function getEmergencyContacts();

    /**
     * Get parents by student
     */
    public function getByStudent($studentId);

    /**
     * Get parent with relationships
     */
    public function getWithRelations($id);

    /**
     * Assign students to parent
     */
    public function assignStudents($parentId, array $studentIds);

    /**
     * Generate parent code
     */
    public function generateParentCode();
}
