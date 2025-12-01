<?php

namespace App\Http\Controllers\Admin\Setup;

use App\DataTables\Admin\Setup\RoleDataTable;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\Setup\RoleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected RoleRepositoryInterface $repository;

    protected $parentViewPath = 'admin.pages.setup.role.';

    protected $parentRoutePath = 'admin.setup.role.';

    public function __construct(RoleRepositoryInterface $repository)
    {
        $this->middleware('auth');
        $this->repository = $repository;
    }

    public function index(RoleDataTable $datatable)
    {
        checkPermissionAndRedirect('admin.setup.role.index');
        Session::put('title', 'Role Management');

        return $datatable->render($this->parentViewPath.'index');
    }

    public function form($id = null)
    {
        checkPermissionAndRedirect('admin.setup.role.'.($id ? 'edit' : 'form'));
        Session::put('title', ($id ? 'Update' : 'Create').' Role');
        $permissionsGrouped = $this->getPermissionsGrouped();
        $processedPermissionsGrouped = [];

        foreach ($permissionsGrouped as $groupName => $permissions) {
            $groupedByModule = collect($permissions)->groupBy(function ($permission) {
                return $permission['display_name'];
            });
            $processedPermissionsGrouped[$groupName] = [
                'permissions' => $permissions,
                'groupedByModule' => $groupedByModule,
            ];
        }

        if ($id) {
            $role = $this->repository->getOne($id);
            if (! $role) {
                flashResponse('Role not found.', 'danger');

                return Redirect::route($this->parentRoutePath.'index');
            }
            $rolePermissions = $role->permissions->pluck('name')->toArray();

            return view($this->parentViewPath.'form', compact('role', 'id', 'processedPermissionsGrouped', 'rolePermissions'));
        }

        $role = null;
        $rolePermissions = [];

        return view($this->parentViewPath.'form', compact('id', 'processedPermissionsGrouped', 'rolePermissions'));
    }

    public function enroll(Request $request)
    {
        $id = $request->input('id');
        checkPermissionAndRedirect('admin.setup.role.'.($id ? 'edit' : 'form'));
        if ($request->has('id') && $request->filled('id')) {
            return $this->update($request);
        }

        $rules = [
            'name' => [
                'required',
                'min:2',
                'max:100',
                'unique:roles,name',
            ],
            'description' => 'nullable|max:255',
            'status' => 'sometimes|in:'.implode(',', Status::values()),
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
        ];

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $role = $this->repository->create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status ?? 1,
                'guard_name' => 'web',
            ]);

            if ($role && $request->has('permissions')) {
                $this->repository->syncPermissions($role->id, $request->permissions);
            }

            DB::commit();

            flashResponse('Role created successfully.', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            flashResponse('Failed to create Role. Please try again.', 'danger');
        }

        return redirect()->route($this->parentRoutePath.'index');
    }

    public function show(string $id)
    {
        checkPermissionAndRedirect('admin.setup.role.show');
        $role = $this->repository->getOne($id);

        if (! $role) {
            flashResponse('Role not found.', 'danger');

            return Redirect::back();
        }

        $permissionsGrouped = $this->getPermissionsGroupedForRole($role);
        $processedPermissionsGrouped = [];

        foreach ($permissionsGrouped as $groupName => $permissions) {
            $permissionsWithRole = collect($permissions)->where('has_permission', true);
            if ($permissionsWithRole->count() > 0) {
                $groupedByModule = $permissionsWithRole->groupBy('display_name');
                $processedPermissionsGrouped[$groupName] = [
                    'permissions' => $permissionsWithRole->toArray(),
                    'groupedByModule' => $groupedByModule,
                ];
            }
        }

        return view($this->parentViewPath.'view', compact('role', 'processedPermissionsGrouped'));
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'min:2',
                'max:100',
                Rule::unique('roles')->ignore($request->id),
            ],
            'description' => 'nullable|max:255',
            'status' => 'sometimes|in:'.implode(',', Status::values()),
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
        ];

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $role = $this->repository->update($request->id, [
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status ?? 1,
            ]);

            if ($role) {
                if ($request->has('permissions')) {
                    $this->repository->syncPermissions($role->id, $request->permissions);
                } else {
                    $this->repository->syncPermissions($role->id, []);
                }
            }

            DB::commit();

            flashResponse('Role updated successfully.', 'warning');
        } catch (\Exception $e) {
            DB::rollBack();
            flashResponse('Failed to update Role. Please try again.', 'danger');
        }

        return redirect()->route($this->parentRoutePath.'index');
    }

    public function delete($id)
    {
        checkPermissionAndRedirect('admin.setup.role.delete');

        $role = $this->repository->getOne($id);

        if (! $role) {
            flashResponse('Role not found.', 'danger');

            return Redirect::back();
        }

        if ($role->name === 'admin') {
            flashResponse('Cannot delete admin role.', 'danger');

            return Redirect::back();
        }

        if ($role->users()->count() > 0) {
            flashResponse('Cannot delete role assigned to users.', 'danger');

            return Redirect::back();
        }

        try {
            DB::beginTransaction();

            $deleted = $this->repository->delete($id);

            DB::commit();

            if ($deleted) {
                flashResponse('Role deleted successfully.', 'danger');
            } else {
                flashResponse('Failed to delete role.', 'danger');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            flashResponse('Failed to delete Role. Please try again.', 'danger');
        }

        return Redirect::back();
    }

    private function getPermissionsGrouped()
    {
        $grouped = [];

        foreach (config('routes_and_permissions') as $menu) {
            $menuName = $menu['name'] ?? 'General';
            if (isset($menu['items'])) {
                foreach ($menu['items'] as $element) {
                    $permissionName = formatPermissionString($element['route']);
                    $permission = Permission::where('name', $permissionName)->first();

                    if ($permission) {
                        $grouped[$menuName][] = [
                            'name' => $permission->name,
                            'display_name' => $element['text'],
                            'icon' => $element['icon'],
                            'action' => $this->getActionFromPermission($permission->name),
                            'slug' => Str::slug($permission->name),
                        ];
                    }

                    if (isset($element['other_selected_routes']) && is_array($element['other_selected_routes'])) {
                        foreach ($element['other_selected_routes'] as $otherRoute) {
                            $otherPermissionName = formatPermissionString($otherRoute);
                            $otherPermission = Permission::where('name', $otherPermissionName)->first();

                            if ($otherPermission) {
                                $grouped[$menuName][] = [
                                    'name' => $otherPermission->name,
                                    'display_name' => $element['text'],
                                    'icon' => $element['icon'],
                                    'action' => $this->getActionFromPermission($otherPermission->name),
                                    'slug' => Str::slug($otherPermission->name),
                                ];
                            }
                        }
                    }

                    if (isset($element['additional_permissions']) && is_array($element['additional_permissions'])) {
                        foreach ($element['additional_permissions'] as $additionalPermission) {
                            $additionalPermissionName = formatPermissionString($additionalPermission);
                            $additionalPermissionModel = Permission::where('name', $additionalPermissionName)->first();

                            if ($additionalPermissionModel) {
                                $grouped[$menuName][] = [
                                    'name' => $additionalPermissionModel->name,
                                    'display_name' => $element['text'],
                                    'icon' => $element['icon'],
                                    'action' => $this->getActionFromPermission($additionalPermissionModel->name),
                                    'slug' => Str::slug($additionalPermissionModel->name),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $grouped;
    }

    private function getActionFromPermission($permissionName)
    {
        $parts = explode('.', $permissionName);
        $action = end($parts);

        return ucfirst(Str::replace('-', ' ', $action));
    }

    private function getPermissionsGroupedForRole($role)
    {
        $grouped = $this->getPermissionsGrouped();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        foreach ($grouped as $menuName => $permissions) {
            foreach ($permissions as $key => $permission) {
                $grouped[$menuName][$key]['has_permission'] = in_array($permission['name'], $rolePermissions);
            }
        }

        return $grouped;
    }
}
