<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    /**
     * Upload profile image
     */
    public function uploadProfileImage(
        UploadedFile $image,
        string $type,
        int $userId,
        ?string $oldImagePath = null
    ): string {
        // Delete old image if exists
        if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
            Storage::disk('public')->delete($oldImagePath);
        }

        // Generate unique filename
        $extension = $image->getClientOriginalExtension();
        $filename = "{$type}_".time()."_{$userId}.{$extension}";

        // Store in appropriate directory
        $directory = $this->getDirectoryForType($type);

        return $image->storeAs($directory, $filename, 'public');
    }

    /**
     * Delete profile image
     */
    public function deleteProfileImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Get storage directory based on user type
     */
    private function getDirectoryForType(string $type): string
    {
        return match (strtolower($type)) {
            'student' => 'students/profiles',
            'teacher' => 'teachers/profiles',
            'security' => 'security/profiles',
            'parent' => 'parents/profiles',
            default => 'users/profiles',
        };
    }

    /**
     * Get profile image URL
     */
    public function getProfileImageUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        return asset('storage/'.$imagePath);
    }
}
