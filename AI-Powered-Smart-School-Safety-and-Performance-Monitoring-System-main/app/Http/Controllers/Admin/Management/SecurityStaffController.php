<?php

namespace App\Http\Controllers\Admin\Management;

use App\DataTables\Admin\Management\SecurityStaffDataTable;
use App\Enums\UserType;
use App\Helpers\ValidationRules;
use App\Http\Controllers\Admin\BaseManagementController;
use App\Repositories\Interfaces\Admin\Management\SecurityStaffRepositoryInterface;
use App\Services\DatabaseTransactionService;
use App\Services\ImageUploadService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class SecurityStaffController extends BaseManagementController
{
    protected string $parentViewPath = 'admin.pages.management.security.';
    protected string $parentRoutePath = 'admin.management.security.';
    protected string $entityName = 'Security Staff';
    protected string $entityType = 'security';

    public function __construct(
        SecurityStaffRepositoryInterface $repository,
        UserService $userService,
        DatabaseTransactionService $transactionService,
        ImageUploadService $imageService
    ) {
        parent::__construct($repository, $userService, $imageService, $transactionService);
    }

    /**
     * Display entity index page with DataTable
     */
    public function index(SecurityStaffDataTable $datatable)
    {
        return $this->renderIndex($datatable, $this->parentViewPath);
    }

    protected function getFormData($id = null): array
    {
        $roles = Role::where('name', 'security')->get();
        return compact('roles');
    }

    protected function getValidationRules(bool $isUpdate = false, $id = null): array
    {
        return ValidationRules::getSecurityStaffRules($isUpdate, $id);
    }

    protected function performCreate(Request $request)
    {
        // Create user account
        $user = $this->userService->createUserWithRole(
            $request->all(),
            UserType::SECURITY,
            $request->input('roles', [])
        );

        // Prepare security data
        $securityData = $request->except(['password', 'password_confirmation', 'roles', 'profile_image']);
        $securityData['user_id'] = $user->id;
        $securityData['is_active'] = true;

        // Handle profile image upload
        $imagePath = $this->handleProfileImageUpload($request);
        if ($imagePath) {
            $securityData['photo_path'] = $imagePath;
        }

        $security = $this->repository->create($securityData);

        // Create notification
        $this->notifyCreated($this->entityName, $security);

        return $security;
    }

    protected function performUpdate(Request $request, $id)
    {
        $security = $this->repository->getById($id);
        if (!$security) {
            throw new \Exception('Security Staff not found.');
        }

        // Update user account
        $userData = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'middle_name' => $request->input('middle_name'),
            'email' => $request->input('email'),
        ];

        if ($request->filled('password')) {
            $userData['password'] = $request->input('password');
        }

        $updatedUser = $this->userService->updateUser($security->user, $userData);

        // Update roles
        if ($request->has('roles')) {
            $this->userService->updateUserRoles($updatedUser, $request->input('roles'));
        }

        // Prepare security staff data
        $securityData = $request->except(['password', 'password_confirmation', 'roles', 'profile_image']);

        // Handle profile image upload
        $imagePath = $this->handleProfileImageUpload($request, $security);
        if ($imagePath) {
            $securityData['photo_path'] = $imagePath;
        }

        $updatedSecurity = $this->repository->update($id, $securityData);

        // Create notification
        $this->notifyUpdated($this->entityName, $updatedSecurity);

        return $updatedSecurity;
    }
}
