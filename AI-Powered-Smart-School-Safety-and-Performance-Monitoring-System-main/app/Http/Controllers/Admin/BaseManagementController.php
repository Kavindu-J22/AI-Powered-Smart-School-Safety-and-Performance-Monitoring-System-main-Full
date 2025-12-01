<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Services\DatabaseTransactionService;
use App\Services\ImageUploadService;
use App\Services\UserService;
use App\Traits\CreatesNotifications;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

abstract class BaseManagementController extends Controller
{
    use CreatesNotifications;

    protected $repository;
    protected UserService $userService;
    protected ImageUploadService $imageService;
    protected DatabaseTransactionService $transactionService;
    protected string $parentViewPath;
    protected string $parentRoutePath;
    protected string $entityName;
    protected string $entityType;

    public function __construct(
        $repository,
        UserService $userService = null,
        ImageUploadService $imageService = null,
        DatabaseTransactionService $transactionService = null
    ) {
        $this->middleware('auth');
        $this->repository = $repository;

        if ($userService) {
            $this->userService = $userService;
        }
        if ($imageService) {
            $this->imageService = $imageService;
        }
        if ($transactionService) {
            $this->transactionService = $transactionService;
        }
    }

    /**
     * Common index logic that can be called by child controllers
     */
    protected function renderIndex($datatable, string $viewPath)
    {
        checkPermissionAndRedirect($this->getPermissionKey('index'));
        Session::put('title', $this->getPageTitle('Management'));

        return $datatable->render($viewPath . 'index');
    }

    /**
     * Show entity details
     */
    public function show(string $id)
    {
        checkPermissionAndRedirect($this->getPermissionKey('show'));

        $entity = $this->repository->getWithRelations($id);

        if (!$entity) {
            flashResponse(Constants::getErrorMessage('not_found', $this->entityName), Constants::FLASH_ERROR);
            return Redirect::back();
        }

        return view($this->parentViewPath . 'view', [$this->getEntityVariableName() => $entity]);
    }

    /**
     * Display form for creating/editing entity
     */
    public function form($id = null)
    {
        $action = $id ? 'edit' : 'form';
        checkPermissionAndRedirect($this->getPermissionKey($action));

        $pageTitle = ($id ? 'Update' : 'Create') . ' ' . $this->entityName;
        Session::put('title', $pageTitle);

        $data = $this->getFormData($id);

        if ($id) {
            $entity = $this->repository->getWithRelations($id);
            if (!$entity) {
                flashResponse(Constants::getErrorMessage('not_found', $this->entityName), Constants::FLASH_ERROR);
                return Redirect::route($this->parentRoutePath . 'index');
            }
            $data[$this->getEntityVariableName()] = $entity;
            $data['id'] = $id;
        } else {
            $data['id'] = $id;
        }

        return view($this->parentViewPath . 'form', $data);
    }

    /**
     * Handle entity creation and update
     */
    public function enroll(Request $request): RedirectResponse
    {
        $id = $request->input('id');
        checkPermissionAndRedirect($this->getPermissionKey($id ? 'edit' : 'form'));

        if ($request->has('id') && $request->filled('id')) {
            return $this->update($request);
        }

        return $this->create($request);
    }

    /**
     * Create new entity
     */
    protected function create(Request $request): RedirectResponse
    {
        $rules = $this->getValidationRules();
        $request->validate($rules);

        $result = $this->transactionService->executeCreate(
            function () use ($request) {
                return $this->performCreate($request);
            },
            $this->entityName
        );

        flashResponse($result['message'], $result['success'] ? Constants::FLASH_SUCCESS : Constants::FLASH_ERROR);
        return redirect()->route($this->parentRoutePath . 'index');
    }

    /**
     * Update existing entity
     */
    protected function update(Request $request): RedirectResponse
    {
        $id = $request->input('id');
        $rules = $this->getValidationRules(true, $id);
        $request->validate($rules);

        $result = $this->transactionService->executeUpdate(
            function () use ($request, $id) {
                return $this->performUpdate($request, $id);
            },
            $this->entityName
        );

        flashResponse($result['message'], $result['success'] ? Constants::FLASH_SUCCESS : Constants::FLASH_ERROR);
        return redirect()->route($this->parentRoutePath . 'index');
    }

    /**
     * Delete entity
     */
    public function delete(string $id): RedirectResponse
    {
        checkPermissionAndRedirect($this->getPermissionKey('delete'));

        $entity = $this->repository->getById($id);
        if (!$entity) {
            flashResponse(Constants::getErrorMessage('not_found', $this->entityName), Constants::FLASH_ERROR);
            return Redirect::back();
        }

        $result = $this->transactionService->executeDelete(
            function () use ($entity, $id) {
                // Create notification before deletion
                if (method_exists($this, 'notifyDeleted')) {
                    $this->notifyDeleted($this->entityName, $entity);
                }

                // Delete user account if exists
                if (isset($entity->user)) {
                    $entity->user->delete();
                }

                // Delete profile image if exists
                if (isset($entity->photo_path) && $entity->photo_path && $this->imageService) {
                    $this->imageService->deleteProfileImage($entity->photo_path);
                }

                // Delete entity
                return $this->repository->delete($id);
            },
            $this->entityName
        );

        flashResponse($result['message'], $result['success'] ? Constants::FLASH_SUCCESS : Constants::FLASH_ERROR);
        return redirect()->route($this->parentRoutePath . 'index');
    }

    /**
     * Handle profile image upload
     */
    protected function handleProfileImageUpload(Request $request, $entity = null): ?string
    {
        if (!$request->hasFile('profile_image') || !$this->imageService) {
            return null;
        }

        $oldImagePath = $entity?->photo_path ?? null;
        $userId = $entity?->user_id ?? time();

        return $this->imageService->uploadProfileImage(
            $request->file('profile_image'),
            $this->entityType,
            $userId,
            $oldImagePath
        );
    }

    /**
     * Get permission key for action
     */
    protected function getPermissionKey(string $action): string
    {
        return str_replace('/', '.', $this->parentRoutePath) . $action;
    }

    /**
     * Get page title
     */
    protected function getPageTitle(string $suffix = ''): string
    {
        return $this->entityName . ($suffix ? " $suffix" : '');
    }

    /**
     * Get entity variable name for views
     */
    protected function getEntityVariableName(): string
    {
        return strtolower($this->entityType);
    }

    // Abstract methods to be implemented by child classes
    abstract protected function getFormData($id = null): array;
    abstract protected function getValidationRules(bool $isUpdate = false, $id = null): array;
    abstract protected function performCreate(Request $request);
    abstract protected function performUpdate(Request $request, $id);
}
