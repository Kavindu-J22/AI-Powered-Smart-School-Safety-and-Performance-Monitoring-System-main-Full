<?php

namespace App\Services;

use App\Enums\Status;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Create a user account with role assignment
     */
    public function createUserWithRole(array $userData, UserType $userType, array $roles): User
    {
        return DB::transaction(function () use ($userData, $userType, $roles): User {
            // Create user account
            $user = User::create([
                'name' => $this->buildFullName($userData),
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'usertype' => $userType->value,
                'status' => Status::ACTIVE->value,
            ]);

            // Assign roles to user
            if (! empty($roles)) {
                $user->assignRole($roles);
            }

            return $user;
        });
    }

    /**
     * Create a parent user with default password
     */
    public function createParentUser(array $parentData): ?User
    {
        $email = $parentData['email'] ?? null;

        if (! $email) {
            return null;
        }

        // Check if user with this email already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            return $existingUser;
        }

        return User::create([
            'name' => $this->buildFullName($parentData),
            'email' => $email,
            'password' => Hash::make(config('app.default_parent_password', 'password123')),
            'usertype' => UserType::PARENT->value,
            'status' => Status::ACTIVE->value,
        ]);
    }

    /**
     * Update user account data
     */
    public function updateUser(User $user, array $userData): User
    {
        $updateData = [
            'name' => $this->buildFullName($userData),
            'email' => $userData['email'],
        ];

        // Only update password if provided
        if (! empty($userData['password'])) {
            $updateData['password'] = Hash::make($userData['password']);
        }

        $user->update($updateData);

        return $user;
    }

    /**
     * Update user roles
     */
    public function updateUserRoles(User $user, array $roles): void
    {
        if (! empty($roles)) {
            $user->syncRoles($roles);
        }
    }

    /**
     * Build full name from name components
     */
    private function buildFullName(array $data): string
    {
        $firstName = $data['first_name'] ?? '';
        $middleName = $data['middle_name'] ?? '';
        $lastName = $data['last_name'] ?? '';

        return trim("$firstName $middleName $lastName");
    }
}
