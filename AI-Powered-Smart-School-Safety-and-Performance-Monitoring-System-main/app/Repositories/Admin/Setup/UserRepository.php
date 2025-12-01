<?php

namespace App\Repositories\Admin\Setup;

use App\Models\User;
use App\Repositories\Interfaces\Admin\Setup\UserRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    public function create($data)
    {
        try {
            DB::beginTransaction();
            $user = User::create($data);
            DB::commit();

            return $user;
        } catch (QueryException $e) {
            Log::error('Failed to create user: '.$e->getMessage());
            DB::rollBack();

            return null;
        }
    }

    public function update($id, $data)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $user->update($data);
            DB::commit();

            return $user;
        } catch (QueryException $e) {
            Log::error('Failed to update user: '.$e->getMessage());
            DB::rollBack();

            return null;
        }
    }

    public function getOne($id)
    {
        try {
            return User::with(['roles', 'permissions'])->find($id); // Returns null if not found
        } catch (QueryException $e) {
            Log::error('Failed to fetch user: '.$e->getMessage());

            return null;
        }
    }

    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return true;
        } catch (QueryException $e) {
            Log::error('Failed to delete user: '.$e->getMessage());

            return false;
        }
    }

    public function getAll()
    {
        try {
            return User::all(); // Replace `available()` with actual logic if needed
        } catch (QueryException $e) {
            Log::error('Failed to fetch users: '.$e->getMessage());

            return collect(); // Empty collection as fallback
        }
    }
}
