<?php

namespace App\Repositories\Interfaces\Admin\Setup;

interface RoleRepositoryInterface
{
    public function create($data);

    public function update($id, $data);

    public function getOne($id);

    public function delete($id);

    public function getAll();

    public function getRoleOptions();

    public function syncPermissions($roleId, array $permissions);

    public function getRolePermissions($roleId);
}
