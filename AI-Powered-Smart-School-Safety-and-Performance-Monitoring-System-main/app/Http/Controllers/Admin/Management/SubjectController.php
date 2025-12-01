<?php

namespace App\Http\Controllers\Admin\Management;

use App\DataTables\Admin\Management\SubjectDataTable;
use App\Helpers\ValidationRules;
use App\Http\Controllers\Admin\BaseManagementController;
use App\Repositories\Interfaces\Admin\Management\SubjectRepositoryInterface;
use App\Services\DatabaseTransactionService;
use App\Services\ImageUploadService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SubjectController extends BaseManagementController
{
    protected string $parentViewPath = 'admin.pages.management.subjects.';
    protected string $parentRoutePath = 'admin.management.subjects.';
    protected string $entityName = 'Subject';
    protected string $entityType = 'subject';

    public function __construct(
        SubjectRepositoryInterface $repository,
        UserService $userService,
        ImageUploadService $imageService,
        DatabaseTransactionService $transactionService
    ) {
        parent::__construct($repository, $userService, $imageService, $transactionService);
    }

    public function index(SubjectDataTable $datatable)
    {
        return $this->renderIndex($datatable, $this->parentViewPath);
    }

    protected function getFormData($id = null): array
    {
        // No additional form data needed for subjects
        return [];
    }

    protected function getValidationRules(bool $isUpdate = false, $id = null): array
    {
        return ValidationRules::getSubjectRules($isUpdate, $id);
    }

    protected function performCreate(Request $request)
    {
        $subjectData = $request->all();
        $subjectData['is_active'] = $request->input('is_active', true);
        
        // Generate subject code if not provided
        if (empty($subjectData['subject_code'])) {
            $subjectData['subject_code'] = $this->generateSubjectCode($subjectData['subject_name']);
        }

        $subject = $this->repository->create($subjectData);
        $this->notifyCreated($this->entityName, $subject);
        
        return $subject;
    }

    protected function performUpdate(Request $request, $id)
    {
        $subject = $this->repository->getById($id);
        if (!$subject) {
            throw new \Exception('Subject not found.');
        }

        $subjectData = $request->all();
        
        // Update subject code if subject name changed
        if ($request->subject_name !== $subject->subject_name && empty($request->subject_code)) {
            $subjectData['subject_code'] = $this->generateSubjectCode($request->subject_name);
        }

        $this->repository->update($id, $subjectData);
        $this->notifyUpdated($this->entityName, $subject);
        
        return $subject;
    }

    public function show(string $id)
    {
        checkPermissionAndRedirect('admin.management.subjects.show');
        $subject = $this->repository->getWithRelations($id);

        if (! $subject) {
            flashResponse('Subject not found.', 'danger');

            return Redirect::back();
        }

        return view($this->parentViewPath.'view', compact('subject'));
    }

    /**
     * Generate subject code from subject name
     */
    private function generateSubjectCode(string $subjectName): string
    {
        // Convert subject name to uppercase and take first 3 letters
        $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $subjectName), 0, 3));
        
        // Add sequential number if needed to make it unique
        $counter = 1;
        $originalCode = $code;
        
        while ($this->repository->existsByCode($code)) {
            $code = $originalCode . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }
        
        return $code;
    }

    protected function performDelete($id)
    {
        $subject = $this->repository->getById($id);
        if (!$subject) {
            throw new \Exception('Subject not found.');
        }

        // Create notification before deletion
        $this->notifyDeleted($this->entityName, $subject);
        
        return $this->repository->delete($id);
    }
}