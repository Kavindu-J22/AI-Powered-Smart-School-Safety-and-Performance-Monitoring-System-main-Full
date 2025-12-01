<?php

namespace App\Repositories\Admin\Setup;

use App\Models\Setting;
use App\Repositories\Interfaces\Admin\Setup\SettingsRepositoryInterface;

class SettingsRepository implements SettingsRepositoryInterface
{
    protected $model = Setting::class;

    public function getLatest()
    {
        return $this->model::active()->latest()->first();
    }

    public function update($data)
    {
        return $this->getLatest()->update($data);
    }
}
