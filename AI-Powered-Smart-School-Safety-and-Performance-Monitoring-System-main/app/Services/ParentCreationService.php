<?php

namespace App\Services;

use App\Models\ParentModel;
use App\Models\User;

class ParentCreationService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Create parents from form array data
     */
    public function createParentsFromArray(array $requestData): array
    {
        $parentIds = [];

        if (! isset($requestData['parent_first_name']) || ! is_array($requestData['parent_first_name'])) {
            return $parentIds;
        }

        foreach ($requestData['parent_first_name'] as $index => $firstName) {
            if (empty($firstName) || empty($requestData['parent_last_name'][$index] ?? '')) {
                continue;
            }

            $parentData = $this->buildParentData($requestData, $index);
            $parent = $this->createSingleParent($parentData, $requestData, $index);

            if ($parent) {
                $parentIds[] = $parent->parent_id;
            }
        }

        return $parentIds;
    }

    /**
     * Create a single parent record
     */
    private function createSingleParent(array $parentData, array $requestData, int $index): ?ParentModel
    {
        // Create user account if email provided
        $parentUser = null;
        $parentEmail = $requestData['parent_email'][$index] ?? null;

        if ($parentEmail) {
            $parentUser = $this->userService->createParentUser([
                'first_name' => $parentData['first_name'],
                'last_name' => $parentData['last_name'],
                'middle_name' => $parentData['middle_name'] ?? '',
                'email' => $parentEmail,
            ]);

            if ($parentUser) {
                $parentUser->assignRole('parent');
            }
        }

        $parentData['user_id'] = $parentUser?->id;

        return ParentModel::create($parentData);
    }

    /**
     * Build parent data array from request
     */
    private function buildParentData(array $requestData, int $index): array
    {
        return [
            'parent_code' => ParentModel::generateParentCode(),
            'first_name' => $requestData['parent_first_name'][$index],
            'middle_name' => $requestData['parent_middle_name'][$index] ?? null,
            'last_name' => $requestData['parent_last_name'][$index],
            'date_of_birth' => $requestData['parent_date_of_birth'][$index] ?? null,
            'gender' => $requestData['parent_gender'][$index],
            'relationship_type' => $requestData['parent_relationship_type'][$index],
            'mobile_phone' => $requestData['parent_mobile_phone'][$index],
            'email' => $requestData['parent_email'][$index] ?? null,
            'occupation' => $requestData['parent_occupation'][$index] ?? null,
            'workplace' => $requestData['parent_workplace'][$index] ?? null,
            'work_phone' => $requestData['parent_work_phone'][$index] ?? null,
            'is_emergency_contact' => $this->isEmergencyContact($requestData, $index),
            'address_line1' => $requestData['parent_address_line1'][$index] ?? null,
            'is_active' => true,
        ];
    }

    /**
     * Check if parent is emergency contact
     */
    private function isEmergencyContact(array $requestData, int $index): bool
    {
        return isset($requestData['parent_is_emergency_contact']) &&
            in_array($index + 1, (array) $requestData['parent_is_emergency_contact']);
    }
}
