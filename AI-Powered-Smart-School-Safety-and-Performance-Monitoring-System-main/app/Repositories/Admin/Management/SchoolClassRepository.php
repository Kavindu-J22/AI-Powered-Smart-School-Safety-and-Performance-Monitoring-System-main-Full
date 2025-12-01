<?php

namespace App\Repositories\Admin\Management;

use App\Models\SchoolClass;
use App\Repositories\Interfaces\Admin\Management\SchoolClassRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SchoolClassRepository implements SchoolClassRepositoryInterface
{
    protected $model;

    public function __construct(SchoolClass $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->with(['classTeacher', 'students', 'subjects'])
            ->orderBy('grade_level', 'asc')
            ->orderBy('class_name', 'asc')
            ->get();
    }

    public function getById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['class_code'])) {
                $data['class_code'] = $this->generateClassCode();
            }
            return $this->model->create($data);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $class = $this->getById($id);
            if (!$class) {
                return false;
            }
            return $class->update($data);
        });
    }

    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            return $this->model->where('id', $id)->delete();
        });
    }

    public function getByGrade($grade)
    {
        return $this->model->with(['classTeacher', 'students'])
            ->where('grade_level', $grade)
            ->orderBy('class_name', 'asc')
            ->get();
    }

    public function getByAcademicYear($year)
    {
        return $this->model->with(['classTeacher', 'students'])
            ->where('academic_year', $year)
            ->orderBy('class_name', 'asc')
            ->get();
    }

    public function getWithRelations($id)
    {
        return $this->model->with(['classTeacher', 'students', 'subjects'])
            ->where('id', $id)
            ->first();
    }

    public function assignSubjects($classId, array $subjectIds)
    {
        $class = $this->getById($classId);
        if (!$class) {
            return false;
        }
        return $class->subjects()->sync($subjectIds);
    }

    public function assignTeacher($classId, $teacherId)
    {
        return $this->update($classId, ['class_teacher_id' => $teacherId]);
    }

    public function generateClassCode()
    {
        $year = date('Y');
        $lastClass = $this->model->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        $sequence = $lastClass ? (int) substr($lastClass->class_code, -4) + 1 : 1;
        return 'CLS' . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}