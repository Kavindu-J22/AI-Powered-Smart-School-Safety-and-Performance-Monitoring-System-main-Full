<?php

namespace App\Repositories\Interfaces\Admin\Management;

interface SecurityStaffRepositoryInterface
{
    /**
     * Get all security staff
     */
    public function getAll();

    /**
     * Get security staff by ID
     */
    public function getById($id);

    /**
     * Create new security staff
     */
    public function create(array $data);

    /**
     * Update security staff
     */
    public function update($id, array $data);

    /**
     * Delete security staff
     */
    public function delete($id);

    /**
     * Get active security staff
     */
    public function getActive();

    /**
     * Get security staff by shift
     */
    public function getByShift($shift);

    /**
     * Get security staff by position
     */
    public function getByPosition($position);

    /**
     * Get security staff with relationships
     */
    public function getWithRelations($id);

    /**
     * Generate security code
     */
    public function generateSecurityCode();
}
