<?php

namespace App\Repositories\Admin\Management;

use App\Models\SecurityStaff;
use App\Repositories\Interfaces\Admin\Management\SecurityStaffRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SecurityStaffRepository implements SecurityStaffRepositoryInterface
{
    protected $model;

    public function __construct(SecurityStaff $model)
    {
        $this->model = $model;
    }

    /**
     * Get all security staff
     */
    public function getAll()
    {
        return $this->model->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get security staff by ID
     */
    public function getById($id)
    {
        return $this->model->with(['user'])
            ->where('security_id', $id)
            ->first();
    }

    /**
     * Create new security staff
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['security_code'])) {
                $data['security_code'] = $this->generateSecurityCode();
            }

            return $this->model->create($data);
        });
    }

    /**
     * Update security staff
     */
    public function update($id, array $data)
    {
        $security = $this->model->where('security_id', $id)->first();

        if (! $security) {
            return false;
        }

        return $security->update($data);
    }

    /**
     * Delete security staff
     */
    public function delete($id)
    {
        return $this->model->where('security_id', $id)->delete();
    }

    /**
     * Get active security staff
     */
    public function getActive()
    {
        return $this->model->with(['user'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get security staff by shift
     */
    public function getByShift($shift)
    {
        return $this->model->with(['user'])
            ->where('shift', $shift)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get security staff by position
     */
    public function getByPosition($position)
    {
        return $this->model->with(['user'])
            ->where('position', $position)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get security staff with relationships
     */
    public function getWithRelations($id)
    {
        return $this->model->with(['user'])
            ->where('security_id', $id)
            ->first();
    }

    /**
     * Generate security code
     */
    public function generateSecurityCode()
    {
        return SecurityStaff::generateSecurityCode();
    }
}
