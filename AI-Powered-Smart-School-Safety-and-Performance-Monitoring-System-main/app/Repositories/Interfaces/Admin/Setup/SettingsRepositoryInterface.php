<?php

namespace App\Repositories\Interfaces\Admin\Setup;

interface SettingsRepositoryInterface
{
    public function getLatest();

    public function update($data);
}
