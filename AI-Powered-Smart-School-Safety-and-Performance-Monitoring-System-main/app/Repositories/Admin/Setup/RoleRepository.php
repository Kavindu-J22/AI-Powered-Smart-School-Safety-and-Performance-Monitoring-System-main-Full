<?php

namespace App\Repositories\Admin\Setup;

use App\Repositories\Interfaces\Admin\Setup\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    protected $model;

    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function update($id, $data)
    {
        $role = $this->model->findOrFail($id);
        $role->update($data);

        return $role;
    }

    public function getOne($id)
    {
        return $this->model->with(['permissions', 'users'])->find($id);
    }

    public function delete($id)
    {
        $role = $this->model->findOrFail($id);

        if ($role->name === 'admin') {
            return false;
        }

        if ($role->users()->count() > 0) {
            return false;
        }

        return $role->delete();
    }

    public function getAll()
    {
        return $this->model->withCount(['permissions', 'users'])->get();
    }

    public function getRoleOptions()
    {
        return $this->model->pluck('name', 'name')->toArray();
    }

    public function syncPermissions($roleId, array $permissions)
    {
        $role = $this->model->findOrFail($roleId);
        $role->syncPermissions($permissions);

        return $role;
    }

    public function getRolePermissions($roleId)
    {
        $role = $this->model->with('permissions')->find($roleId);

        return $role ? $role->permissions->pluck('name')->toArray() : [];
    }
}
