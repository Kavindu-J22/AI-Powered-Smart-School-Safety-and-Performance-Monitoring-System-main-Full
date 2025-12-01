<?php

namespace App\Repositories\Interfaces\Admin\Setup;

interface UserRepositoryInterface
{
    public function create($data);

    public function update($id, $data);

    public function getOne($id);

    public function delete($id);

    public function getAll();
}
