<?php

namespace App\Repositories\Admin\Management;

use App\Models\ParentModel;
use App\Repositories\Interfaces\Admin\Management\ParentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ParentRepository implements ParentRepositoryInterface
{
    protected $model;

    public function __construct(ParentModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get all parents
     */
    public function getAll()
    {
        return $this->model->with(['user', 'students'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get parent by ID
     */
    public function getById($id)
    {
        return $this->model->with(['user', 'students'])
            ->where('parent_id', $id)
            ->first();
    }

    /**
     * Create new parent
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['parent_code'])) {
                $data['parent_code'] = $this->generateParentCode();
            }

            $parent = $this->model->create($data);

            // Assign students if provided
            if (isset($data['students'])) {
                $this->assignStudents($parent->parent_id, $data['students']);
            }

            return $parent;
        });
    }

    /**
     * Update parent
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $parent = $this->model->where('parent_id', $id)->first();

            if (! $parent) {
                return false;
            }

            $parent->update($data);

            // Update students if provided
            if (isset($data['students'])) {
                $this->assignStudents($id, $data['students']);
            }

            return $parent;
        });
    }

    /**
     * Delete parent
     */
    public function delete($id)
    {
        return $this->model->where('parent_id', $id)->delete();
    }

    /**
     * Get active parents
     */
    public function getActive()
    {
        return $this->model->with(['user', 'students'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get emergency contacts
     */
    public function getEmergencyContacts()
    {
        return $this->model->with(['user', 'students'])
            ->where('is_emergency_contact', true)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get parents by student
     */
    public function getByStudent($studentId)
    {
        return $this->model->with(['user'])
            ->whereHas('students', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get parent with relationships
     */
    public function getWithRelations($id)
    {
        return $this->model->with(['user', 'students'])
            ->where('parent_id', $id)
            ->first();
    }

    /**
     * Assign students to parent
     */
    public function assignStudents($parentId, array $studentIds)
    {
        $parent = $this->model->where('parent_id', $parentId)->first();

        if (! $parent) {
            return false;
        }

        return $parent->students()->sync($studentIds);
    }

    /**
     * Generate parent code
     */
    public function generateParentCode()
    {
        return ParentModel::generateParentCode();
    }
}
