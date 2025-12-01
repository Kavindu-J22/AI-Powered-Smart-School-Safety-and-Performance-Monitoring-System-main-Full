<?php

namespace App\Http\Controllers\Admin\Management;

use App\DataTables\Admin\Management\SchoolClassDataTable;
use App\Helpers\Constants;
use App\Helpers\ValidationRules;
use App\Http\Controllers\Admin\BaseManagementController;
use App\Repositories\Interfaces\Admin\Management\SchoolClassRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\SubjectRepositoryInterface;
use App\Repositories\Interfaces\Admin\Management\TeacherRepositoryInterface;
use App\Services\DatabaseTransactionService;
use App\Services\ImageUploadService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SchoolClassController extends BaseManagementController
{
    protected string $parentViewPath = 'admin.pages.management.classes.';
    protected string $parentRoutePath = 'admin.management.classes.';
    protected string $entityName = 'School Class';
    protected string $entityType = 'class';

    protected TeacherRepositoryInterface $teacherRepository;

    protected SubjectRepositoryInterface $subjectRepository;

    public function __construct(
        SchoolClassRepositoryInterface $repository,
        TeacherRepositoryInterface $teacherRepository,
        SubjectRepositoryInterface $subjectRepository,
        UserService $userService,
        ImageUploadService $imageService,
        DatabaseTransactionService $transactionService
    ) {
        parent::__construct($repository, $userService, $imageService, $transactionService);
        $this->teacherRepository = $teacherRepository;
        $this->subjectRepository = $subjectRepository;
        $this->transactionService = $transactionService;
    }

    public function index(SchoolClassDataTable $datatable)
    {
        return $this->renderIndex($datatable, $this->parentViewPath);
    }

    protected function getFormData($id = null): array
    {
        // Get available teachers (not assigned to other classes)
        // If editing, exclude the current class so its teacher can remain assigned
        $teachers = $this->teacherRepository->getAvailableClassTeachers($id);
        $subjects = $this->subjectRepository->getAll();
        return compact('teachers', 'subjects');
    }

    public function form($id = null)
    {
        return parent::form($id);
    }

    protected function getValidationRules(bool $isUpdate = false, $id = null): array
    {
        return ValidationRules::getSchoolClassRules($isUpdate, $id);
    }

    protected function performCreate(Request $request)
    {
        $classData = $request->except(['subjects']);
        $classData['is_active'] = $request->input('is_active', true);
        $class = $this->repository->create($classData);

        // Assign subjects if provided
        if ($request->has('subjects') && !empty($request->input('subjects'))) {
            $this->repository->assignSubjects($class->id, $request->input('subjects'));
        }

        $this->notifyCreated($this->entityName, $class);
        return $class;
    }

    protected function performUpdate(Request $request, $id)
    {
        $class = $this->repository->getById($id);
        if (!$class) {
            throw new \Exception('School Class not found.');
        }

        $classData = $request->except(['subjects']);
        $this->repository->update($id, $classData);

        // Update subjects if provided
        if ($request->has('subjects')) {
            $this->repository->assignSubjects($id, $request->input('subjects', []));
        }

        $this->notifyUpdated($this->entityName, $class);
        return $class;
    }

    public function show(string $id)
    {
        checkPermissionAndRedirect('admin.management.classes.show');
        $class = $this->repository->getWithRelations($id);

        if (! $class) {
            flashResponse('Class not found.', 'danger');

            return Redirect::back();
        }

        return view($this->parentViewPath . 'view', compact('class'));
    }
}
