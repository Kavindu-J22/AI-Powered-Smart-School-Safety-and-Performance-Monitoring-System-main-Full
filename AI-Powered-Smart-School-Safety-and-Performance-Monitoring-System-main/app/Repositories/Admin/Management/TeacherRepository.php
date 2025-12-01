<?php

namespace App\Repositories\Admin\Management;

use App\Models\Teacher;
use App\Repositories\Interfaces\Admin\Management\TeacherRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TeacherRepository implements TeacherRepositoryInterface
{
    protected $model;

    public function __construct(Teacher $model)
    {
        $this->model = $model;
    }

    /**
     * Get all teachers
     */
    public function getAll()
    {
        return $this->model->with(['user', 'subjects'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get teacher by ID
     */
    public function getById($id)
    {
        return $this->model->with(['user', 'subjects', 'classes'])
            ->where('teacher_id', $id)
            ->first();
    }

    /**
     * Create new teacher
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['teacher_code'])) {
                $data['teacher_code'] = $this->generateTeacherCode();
            }

            $teacher = $this->model->create($data);

            // Assign subjects if provided
            if (isset($data['subjects'])) {
                $this->assignSubjects($teacher->teacher_id, $data['subjects']);
            }

            return $teacher;
        });
    }

    /**
     * Update teacher
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $teacher = $this->model->where('teacher_id', $id)->first();

            if (! $teacher) {
                return false;
            }

            $teacher->update($data);

            // Update subjects if provided
            if (isset($data['subjects'])) {
                $this->assignSubjects($id, $data['subjects']);
            }

            return $teacher;
        });
    }

    /**
     * Delete teacher
     */
    public function delete($id)
    {
        return $this->model->where('teacher_id', $id)->delete();
    }

    /**
     * Get active teachers
     */
    public function getActive()
    {
        return $this->model->with(['user', 'subjects'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get class teachers
     * Returns all active teachers who can be assigned as class teachers
     */
    public function getClassTeachers()
    {
        return $this->model->with(['user', 'subjects'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get available teachers for class assignment
     * Returns active teachers who are not already assigned to a class
     */
    public function getAvailableClassTeachers($excludeClassId = null)
    {
        $query = $this->model->with(['user', 'subjects'])
            ->where('is_active', true)
            ->whereDoesntHave('assignedClass', function ($query) use ($excludeClassId) {
                if ($excludeClassId) {
                    $query->where('id', '!=', $excludeClassId);
                }
            })
            ->orderBy('created_at', 'desc');

        return $query->get();
    }

    /**
     * Get teachers by subject
     */
    public function getBySubject($subjectId)
    {
        return $this->model->with(['user'])
            ->whereHas('subjects', function ($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get teacher with relationships
     */
    public function getWithRelations($id)
    {
        return $this->model->with(['user', 'subjects', 'classes'])
            ->where('teacher_id', $id)
            ->first();
    }

    /**
     * Assign subjects to teacher
     */
    public function assignSubjects($teacherId, array $subjectIds)
    {
        $teacher = $this->model->where('teacher_id', $teacherId)->first();

        if (! $teacher) {
            return false;
        }

        return $teacher->subjects()->sync($subjectIds);
    }

    /**
     * Generate teacher code
     */
    public function generateTeacherCode()
    {
        return Teacher::generateTeacherCode();
    }
}
