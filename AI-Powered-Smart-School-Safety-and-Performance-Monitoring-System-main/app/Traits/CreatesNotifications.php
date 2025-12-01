<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

trait CreatesNotifications
{
    protected function createNotification(string $type, string $entityType, $entity, array $additionalData = [])
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        $entityName = $this->getEntityDisplayName($entityType, $entity);

        // Get the primary key value - handle different entity types
        $entityId = $this->getEntityId($entity);

        if (!$entityId) {
            return; // Skip notification if entity ID not found
        }

        $notification = Notification::create([
            'type' => $type,
            'title' => $this->generateTitle($type, $entityType),
            'message' => $this->generateMessage($type, $entityType, $entityName, $user->name),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'is_read' => false,
            'data' => array_merge([
                'entity_name' => $entityName,
                'action_at' => now()->toISOString(),
            ], $additionalData),
        ]);

        return $notification;
    }

    /**
     * Get the primary key value for different entity types
     */
    protected function getEntityId($entity)
    {
        // Try common ID field names
        $idFields = ['id', 'student_id', 'teacher_id', 'parent_id', 'staff_id', 'security_staff_id'];

        foreach ($idFields as $field) {
            if (isset($entity->$field) && $entity->$field) {
                return $entity->$field;
            }
        }

        // Try getting the primary key dynamically
        if (method_exists($entity, 'getKey')) {
            return $entity->getKey();
        }

        return null;
    }

    protected function generateTitle(string $type, string $entityType): string
    {
        $actionMap = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
        ];

        $action = $actionMap[$type] ?? ucfirst($type);

        return "{$entityType} {$action}";
    }

    protected function generateMessage(string $type, string $entityType, string $entityName, string $userName): string
    {
        $messages = [
            'created' => "{$userName} created a new {$entityType}: {$entityName}",
            'updated' => "{$userName} updated {$entityType}: {$entityName}",
            'deleted' => "{$userName} deleted {$entityType}: {$entityName}",
        ];

        return $messages[$type] ?? "{$userName} performed {$type} action on {$entityType}: {$entityName}";
    }

    protected function getEntityDisplayName(string $entityType, $entity): string
    {
        // Try common name fields
        $nameFields = ['name', 'full_name', 'title', 'subject_name', 'class_name'];

        foreach ($nameFields as $field) {
            if (isset($entity->$field)) {
                return $entity->$field;
            }
        }

        // For students, combine first and last name
        if ($entityType === 'Student' && isset($entity->first_name)) {
            return trim($entity->first_name . ' ' . ($entity->last_name ?? ''));
        }

        // For security staff, use first and last name
        if ($entityType === 'SecurityStaff' && isset($entity->first_name)) {
            return trim($entity->first_name . ' ' . ($entity->last_name ?? ''));
        }

        // Fallback to ID
        return "#{$entity->id}";
    }

    protected function notifyCreated(string $entityType, $entity, array $additionalData = [])
    {
        return $this->createNotification('created', $entityType, $entity, $additionalData);
    }

    protected function notifyUpdated(string $entityType, $entity, array $additionalData = [])
    {
        return $this->createNotification('updated', $entityType, $entity, $additionalData);
    }

    protected function notifyDeleted(string $entityType, $entity, array $additionalData = [])
    {
        return $this->createNotification('deleted', $entityType, $entity, $additionalData);
    }
}
