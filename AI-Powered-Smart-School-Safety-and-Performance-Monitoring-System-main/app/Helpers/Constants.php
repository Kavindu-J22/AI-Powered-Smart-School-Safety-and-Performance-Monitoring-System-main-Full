<?php

namespace App\Helpers;

class Constants
{
    // Default Values
    const DEFAULT_PARENT_PASSWORD = 'password123';

    const DEFAULT_PROFILE_IMAGE_SIZE = 2048; // KB

    const DEFAULT_PHONE_LENGTH = 15;

    const DEFAULT_NAME_MIN_LENGTH = 2;

    const DEFAULT_NAME_MAX_LENGTH = 50;

    // File Upload Settings
    const ALLOWED_IMAGE_EXTENSIONS = ['jpeg', 'png', 'jpg', 'gif'];

    const MAX_IMAGE_SIZE_KB = 2048;

    // Grade Levels
    const MIN_GRADE_LEVEL = 1;

    const MAX_GRADE_LEVEL = 13;

    // Entity Code Formats
    const STUDENT_CODE_PREFIX = 'stu-';

    const TEACHER_CODE_PREFIX = 'te-';

    const PARENT_CODE_PREFIX = 'par-';

    const SECURITY_CODE_PREFIX = 'sec-';

    const CODE_LENGTH = 8;

    // Flash Message Types
    const FLASH_SUCCESS = 'success';

    const FLASH_ERROR = 'error';

    const FLASH_WARNING = 'warning';

    const FLASH_INFO = 'info';

    // Common Status Messages
    const MSG_NOT_FOUND = '%s not found.';

    const MSG_CREATED_SUCCESS = '%s created successfully.';

    const MSG_UPDATED_SUCCESS = '%s updated successfully.';

    const MSG_DELETED_SUCCESS = '%s deleted successfully.';

    const MSG_CREATE_FAILED = 'Failed to create %s. Please try again.';

    const MSG_UPDATE_FAILED = 'Failed to update %s. Please try again.';

    const MSG_DELETE_FAILED = 'Failed to delete %s. Please try again.';

    const MSG_UNAUTHORIZED = 'Unauthorized action. You do not have permission to access this resource.';

    // User Types
    const USER_TYPE_ADMIN = 'admin';
    const USER_TYPE_TEACHER = 'teacher';
    const USER_TYPE_STUDENT = 'student';
    const USER_TYPE_PARENT = 'parent';
    const USER_TYPE_SECURITY = 'security';

    // Gender Options
    const GENDER_MALE = 'M';
    const GENDER_FEMALE = 'F';
    const GENDER_OTHER = 'Other';

    // Status Options
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Subject Types
    const SUBJECT_TYPE_CORE = 'Core';
    const SUBJECT_TYPE_ELECTIVE = 'Elective';
    const SUBJECT_TYPE_OPTIONAL = 'Optional';

    // Shifts for Security Staff
    const SHIFT_MORNING = 'Morning';
    const SHIFT_AFTERNOON = 'Afternoon';
    const SHIFT_NIGHT = 'Night';

    // Relationship Types
    const RELATIONSHIP_FATHER = 'Father';
    const RELATIONSHIP_MOTHER = 'Mother';
    const RELATIONSHIP_GUARDIAN = 'Guardian';
    const RELATIONSHIP_OTHER = 'Other';

    /**
     * Get all gender options
     */
    public static function getGenderOptions(): array
    {
        return [
            self::GENDER_MALE => 'Male',
            self::GENDER_FEMALE => 'Female',
            self::GENDER_OTHER => 'Other',
        ];
    }

    /**
     * Get all user type options
     */
    public static function getUserTypeOptions(): array
    {
        return [
            self::USER_TYPE_ADMIN => 'Admin',
            self::USER_TYPE_TEACHER => 'Teacher',
            self::USER_TYPE_STUDENT => 'Student',
            self::USER_TYPE_PARENT => 'Parent',
            self::USER_TYPE_SECURITY => 'Security',
        ];
    }

    /**
     * Get all shift options
     */
    public static function getShiftOptions(): array
    {
        return [
            self::SHIFT_MORNING => 'Morning',
            self::SHIFT_AFTERNOON => 'Afternoon',
            self::SHIFT_NIGHT => 'Night',
        ];
    }

    /**
     * Get all subject type options
     */
    public static function getSubjectTypeOptions(): array
    {
        return [
            self::SUBJECT_TYPE_CORE => 'Core',
            self::SUBJECT_TYPE_ELECTIVE => 'Elective',
            self::SUBJECT_TYPE_OPTIONAL => 'Optional',
        ];
    }

    /**
     * Get all relationship type options
     */
    public static function getRelationshipTypeOptions(): array
    {
        return [
            self::RELATIONSHIP_FATHER => 'Father',
            self::RELATIONSHIP_MOTHER => 'Mother',
            self::RELATIONSHIP_GUARDIAN => 'Guardian',
            self::RELATIONSHIP_OTHER => 'Other',
        ];
    }

    /**
     * Get formatted success message
     */
    public static function getSuccessMessage(string $action, string $entity): string
    {
        $template = match (strtolower($action)) {
            'create', 'created' => self::MSG_CREATED_SUCCESS,
            'update', 'updated' => self::MSG_UPDATED_SUCCESS,
            'delete', 'deleted' => self::MSG_DELETED_SUCCESS,
            default => '%s processed successfully.'
        };

        return sprintf($template, $entity);
    }

    /**
     * Get formatted error message
     */
    public static function getErrorMessage(string $action, string $entity): string
    {
        $template = match (strtolower($action)) {
            'create', 'created' => self::MSG_CREATE_FAILED,
            'update', 'updated' => self::MSG_UPDATE_FAILED,
            'delete', 'deleted' => self::MSG_DELETE_FAILED,
            'not_found' => self::MSG_NOT_FOUND,
            default => 'Failed to process %s. Please try again.'
        };

        return sprintf($template, $entity);
    }

    /**
     * Get entity code prefix
     */
    public static function getCodePrefix(string $entityType): string
    {
        return match (strtolower($entityType)) {
            'student' => self::STUDENT_CODE_PREFIX,
            'teacher' => self::TEACHER_CODE_PREFIX,
            'parent' => self::PARENT_CODE_PREFIX,
            'security' => self::SECURITY_CODE_PREFIX,
            default => 'ent-'
        };
    }
}
