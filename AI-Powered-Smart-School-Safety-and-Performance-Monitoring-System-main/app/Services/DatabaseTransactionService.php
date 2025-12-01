<?php

namespace App\Services;

use App\Traits\CreatesNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseTransactionService
{
    use CreatesNotifications;

    /**
     * Execute database transaction with proper error handling
     */
    public function executeTransaction(callable $operation, string $successMessage, string $errorMessage): array
    {
        try {
            DB::beginTransaction();

            $result = $operation();

            DB::commit();

            return [
                'success' => true,
                'message' => $successMessage,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($errorMessage . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute create operation with notifications
     */
    public function executeCreate(
        callable $operation,
        string $entityType,
        ?string $successMessage = null,
        ?string $errorMessage = null
    ): array {
        $successMessage = $successMessage ?? "{$entityType} created successfully.";
        $errorMessage = $errorMessage ?? "Failed to create {$entityType}. Please try again.";

        $result = $this->executeTransaction($operation, $successMessage, $errorMessage);

        if ($result['success'] && isset($result['data'])) {
            $this->notifyCreated($entityType, $result['data']);
        }

        return $result;
    }

    /**
     * Execute update operation with notifications
     */
    public function executeUpdate(
        callable $operation,
        string $entityType,
        mixed $entity = null,
        ?string $successMessage = null,
        ?string $errorMessage = null
    ): array {
        $successMessage = $successMessage ?? "{$entityType} updated successfully.";
        $errorMessage = $errorMessage ?? "Failed to update {$entityType}. Please try again.";

        $result = $this->executeTransaction($operation, $successMessage, $errorMessage);

        if ($result['success'] && $entity) {
            $this->notifyUpdated($entityType, $entity);
        }

        return $result;
    }

    /**
     * Execute delete operation with notifications
     */
    public function executeDelete(
        callable $operation,
        string $entityType,
        mixed $entity = null,
        ?string $successMessage = null,
        ?string $errorMessage = null
    ): array {
        // Create notification before deletion
        if ($entity) {
            $this->notifyDeleted($entityType, $entity);
        }

        $successMessage = $successMessage ?? "{$entityType} deleted successfully.";
        $errorMessage = $errorMessage ?? "Failed to delete {$entityType}. Please try again.";

        return $this->executeTransaction($operation, $successMessage, $errorMessage);
    }
}
