<?php

namespace App\Repositories\Admin\Management;

use App\Models\Subject;
use App\Repositories\Interfaces\Admin\Management\SubjectRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SubjectRepository implements SubjectRepositoryInterface
{
    protected $model;

    public function __construct(Subject $model)
    {
        $this->model = $model;
    }

    /**
     * Get all subjects
     */
    public function getAll()
    {
        return $this->model->with(['teachers', 'classes'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get subject by ID
     */
    public function getById($id)
    {
        return $this->model->with(['teachers', 'classes', 'students'])
            ->where('id', $id)
            ->first();
    }

    /**
     * Create new subject
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['subject_code'])) {
                $data['subject_code'] = $this->generateSubjectCode();
            }

            return $this->model->create($data);
        });
    }

    /**
     * Update subject
     */
    public function update($id, array $data)
    {
        $subject = $this->model->where('id', $id)->first();

        if (! $subject) {
            return false;
        }

        return $subject->update($data);
    }

    /**
     * Delete subject
     */
    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    /**
     * Get subjects by grade level
     */
    public function getByGrade($grade)
    {
        return $this->model->with(['teachers'])
            ->where('grade_level', $grade)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get subjects by type
     */
    public function getByType($type)
    {
        return $this->model->with(['teachers'])
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get subject with relationships
     */
    public function getWithRelations($id)
    {
        return $this->model->with(['teachers', 'classes', 'students'])
            ->where('id', $id)
            ->first();
    }

    /**
     * Get subjects for class
     */
    public function getForClass($classId)
    {
        return $this->model->with(['teachers'])
            ->whereHas('classes', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get subjects for teacher
     */
    public function getForTeacher($teacherId)
    {
        return $this->model->with(['classes'])
            ->whereHas('teachers', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate subject code
     */
    public function generateSubjectCode()
    {
        $year = date('Y');
        $lastSubject = $this->model->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastSubject ? (int) substr($lastSubject->subject_code, -4) + 1 : 1;

        return 'SUB'.$year.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if subject code exists
     */
    public function existsByCode(string $code): bool
    {
        return $this->model->where('subject_code', $code)->exists();
    }
}
