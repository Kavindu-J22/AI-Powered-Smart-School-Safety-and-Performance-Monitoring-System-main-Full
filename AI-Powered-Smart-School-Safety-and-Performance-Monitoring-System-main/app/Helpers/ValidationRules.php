<?php

namespace App\Helpers;

use App\Enums\Gender;
use App\Enums\RelationshipType;
use Illuminate\Validation\Rule;

class ValidationRules
{
    // Common field validation rules
    public const PERSONAL_NAME_RULES = 'required|min:2|max:50';

    public const OPTIONAL_NAME_RULES = 'nullable|max:50';

    public const EMAIL_RULES = 'required|email|max:255';

    public const PASSWORD_RULES = 'required|min:8|confirmed';

    public const OPTIONAL_PASSWORD_RULES = 'nullable|min:8|confirmed';

    public const PHONE_RULES = 'nullable|max:15';

    public const REQUIRED_PHONE_RULES = 'required|max:15';

    public const DATE_RULES = 'required|date|before:today';

    public const PROFILE_IMAGE_RULES = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';

    public const NUMERIC_RULES = 'nullable|numeric|min:0';

    public const REQUIRED_NUMERIC_RULES = 'required|numeric|min:0';

    public const BOOLEAN_RULES = 'boolean';

    public const GRADE_LEVEL_RULES = 'required|integer|min:1|max:13';

    public const ADDRESS_RULES = 'nullable|max:255';

    public const GENDER_RULES = 'required|in:' . Constants::GENDER_MALE . ',' . Constants::GENDER_FEMALE . ',' . Constants::GENDER_OTHER;

    public const SHIFT_RULES = 'required|in:' . Constants::SHIFT_MORNING . ',' . Constants::SHIFT_AFTERNOON . ',' . Constants::SHIFT_NIGHT;

    public const SUBJECT_TYPE_RULES = 'required|in:' . Constants::SUBJECT_TYPE_CORE . ',' . Constants::SUBJECT_TYPE_ELECTIVE . ',' . Constants::SUBJECT_TYPE_OPTIONAL;

    public const STATUS_RULES = 'required|in:' . Constants::STATUS_ACTIVE . ',' . Constants::STATUS_INACTIVE;

    /**
     * Get common person validation rules
     */
    public static function getPersonRules(bool $isUpdate = false, ?int $userId = null): array
    {
        if ($isUpdate && $userId) {
            $emailRule = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'id'),
            ];
        } else {
            $emailRule = self::EMAIL_RULES . '|unique:users,email';
        }

        return [
            'first_name' => self::PERSONAL_NAME_RULES,
            'last_name' => self::PERSONAL_NAME_RULES,
            'middle_name' => self::OPTIONAL_NAME_RULES,
            'date_of_birth' => self::DATE_RULES,
            'gender' => self::GENDER_RULES,
            'email' => $emailRule,
            'password' => $isUpdate ? self::OPTIONAL_PASSWORD_RULES : self::PASSWORD_RULES,
            'profile_image' => self::PROFILE_IMAGE_RULES,
            'mobile_phone' => self::PHONE_RULES,
            'home_phone' => self::PHONE_RULES,
            'nationality' => self::OPTIONAL_NAME_RULES,
            'address_line1' => self::ADDRESS_RULES,
            'address_line2' => self::ADDRESS_RULES,
            'city' => self::OPTIONAL_NAME_RULES,
            'state' => self::OPTIONAL_NAME_RULES,
            'postal_code' => 'nullable|max:20',
            'country' => self::OPTIONAL_NAME_RULES,
        ];
    }

    /**
     * Get student specific validation rules
     */
    public static function getStudentRules(bool $isUpdate = false, ?int $userId = null): array
    {
        $rules = self::getPersonRules($isUpdate, $userId);

        return array_merge($rules, [
            'grade_level' => self::GRADE_LEVEL_RULES,
            'class_id' => 'nullable|exists:school_classes,id',
            'section' => 'nullable|max:10',
            'enrollment_date' => 'required|date',
            'religion' => self::OPTIONAL_NAME_RULES,
            'home_language' => self::OPTIONAL_NAME_RULES,
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'parents' => 'nullable|array',
            'parents.*' => 'exists:parents,parent_id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
    }

    /**
     * Get teacher specific validation rules
     */
    public static function getTeacherRules(bool $isUpdate = false, ?int $userId = null): array
    {
        $rules = self::getPersonRules($isUpdate, $userId);

        return array_merge($rules, [
            'qualification' => 'required|max:255',
            'specialization' => 'nullable|max:255',
            'experience_years' => self::NUMERIC_RULES,
            'joining_date' => 'required|date',
            'employee_id' => 'nullable|max:50',
            'teaching_level' => 'required|in:Primary,Secondary,Arts,Commerce,Science,Technology',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
    }

    /**
     * Get parent validation rules for array inputs
     */
    public static function getParentArrayRules(): array
    {
        return [
            'parent_first_name' => 'nullable|array',
            'parent_first_name.*' => 'required_with:parent_last_name.*|max:50',
            'parent_last_name' => 'nullable|array',
            'parent_last_name.*' => 'required_with:parent_first_name.*|max:50',
            'parent_middle_name' => 'nullable|array',
            'parent_middle_name.*' => 'nullable|max:50',
            'parent_gender' => 'nullable|array',
            'parent_gender.*' => 'required_with:parent_first_name.*|' . self::GENDER_RULES,
            'parent_relationship_type' => 'nullable|array',
            'parent_relationship_type.*' => 'required_with:parent_first_name.*|' . RelationshipType::getValidationRule(),
            'parent_mobile_phone' => 'nullable|array',
            'parent_mobile_phone.*' => 'required_with:parent_first_name.*|max:15',
            'parent_email' => 'nullable|array',
            'parent_email.*' => 'nullable|email|max:100',
            'parent_date_of_birth' => 'nullable|array',
            'parent_date_of_birth.*' => 'nullable|date|before:today',
            'parent_occupation' => 'nullable|array',
            'parent_occupation.*' => 'nullable|max:100',
            'parent_workplace' => 'nullable|array',
            'parent_workplace.*' => 'nullable|max:100',
            'parent_work_phone' => 'nullable|array',
            'parent_work_phone.*' => 'nullable|max:15',
            'parent_address_line1' => 'nullable|array',
            'parent_address_line1.*' => 'nullable|max:255',
        ];
    }

    /**
     * Get security staff specific validation rules
     */
    public static function getSecurityStaffRules(bool $isUpdate = false, ?int $userId = null): array
    {
        $rules = self::getPersonRules($isUpdate, $userId);

        return array_merge($rules, [
            'joining_date' => 'required|date',
            'employee_id' => 'nullable|max:50',
            'shift' => self::SHIFT_RULES,
            'position' => 'required|max:100',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
    }

    /**
     * Get parent specific validation rules (single parent)
     */
    public static function getParentRules(bool $isUpdate = false, ?int $userId = null): array
    {
        $rules = self::getPersonRules($isUpdate, $userId);

        return array_merge($rules, [
            'relationship_type' => 'required|' . RelationshipType::getValidationRule(),
            'occupation' => 'nullable|max:100',
            'workplace' => 'nullable|max:100',
            'work_phone' => self::PHONE_RULES,
            'is_emergency_contact' => self::BOOLEAN_RULES,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
    }

    /**
     * Get subject validation rules
     */
    public static function getSubjectRules(bool $isUpdate = false, ?int $id = null): array
    {
        $rules = [
            'subject_name' => 'required|min:2|max:100',
            'subject_code' => 'required|max:20',
            'grade_level' => self::GRADE_LEVEL_RULES,
            'description' => 'nullable|max:1000',
            'credits' => 'required|integer|min:1|max:10',
            'type' => 'required|in:Core,Elective,Optional',
            'is_active' => self::BOOLEAN_RULES,
        ];

        if ($isUpdate && $id) {
            $rules['subject_name'] = [
                'required',
                'min:2',
                'max:100',
                Rule::unique('subjects', 'subject_name')->ignore($id, 'id'),
            ];
            $rules['subject_code'] = [
                'required',
                'max:20',
                Rule::unique('subjects', 'subject_code')->ignore($id, 'id'),
            ];
        } else {
            $rules['subject_name'] .= '|unique:subjects,subject_name';
            $rules['subject_code'] .= '|unique:subjects,subject_code';
        }

        return $rules;
    }

    /**
     * Get user management validation rules
     */
    public static function getUserRules(bool $isUpdate = false, ?int $userId = null): array
    {
        if ($isUpdate && $userId) {
            $emailRule = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'id'),
            ];
        } else {
            $emailRule = self::EMAIL_RULES . '|unique:users,email';
        }

        return [
            'name' => self::PERSONAL_NAME_RULES,
            'email' => $emailRule,
            'password' => $isUpdate ? self::OPTIONAL_PASSWORD_RULES : self::PASSWORD_RULES,
            'usertype' => 'required|in:admin,teacher,student,parent,security',
            'status' => 'required|in:active,inactive',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ];
    }

    /**
     * Get common validation rules for all entities with users
     */
    public static function getCommonEntityWithUserRules(bool $isUpdate = false, ?int $userId = null): array
    {
        if ($isUpdate && $userId) {
            $emailRule = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'id'),
            ];
        } else {
            $emailRule = self::EMAIL_RULES . '|unique:users,email';
        }

        return [
            'email' => $emailRule,
            'password' => $isUpdate ? self::OPTIONAL_PASSWORD_RULES : self::PASSWORD_RULES,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'profile_image' => self::PROFILE_IMAGE_RULES,
        ];
    }

    /**
     * Get settings validation rules
     */
    public static function getSettingsRules(): array
    {
        return [
            'school_name' => 'required|max:255',
            'school_address' => 'required|max:500',
            'school_phone' => self::REQUIRED_PHONE_RULES,
            'school_email' => self::EMAIL_RULES,
            'academic_year_start' => 'required|date',
            'academic_year_end' => 'required|date|after:academic_year_start',
            'timezone' => 'required|max:100',
            'date_format' => 'required|in:Y-m-d,d/m/Y,m/d/Y,d-m-Y',
            'time_format' => 'required|in:H:i,h:i A',
            'currency' => 'required|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public static function getSchoolClassRules(bool $isUpdate = false, ?int $id = null): array
    {
        $rules = [
            'class_name' => 'required|min:2|max:100',
            'grade_level' => self::GRADE_LEVEL_RULES,
            'academic_year' => 'required|max:10',
            'section' => 'nullable|max:10',
            'class_teacher_id' => 'nullable|exists:teachers,teacher_id',
            'room_number' => 'nullable|max:20',
            'capacity' => 'required|integer|min:1|max:200',
            'description' => 'nullable|max:1000',
            'is_active' => self::BOOLEAN_RULES,
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];

        if ($isUpdate && $id) {
            $rules['class_name'] = [
                'required',
                'min:2',
                'max:100',
                Rule::unique('school_classes', 'class_name')->ignore($id, 'id'),
            ];
        } else {
            $rules['class_name'] .= '|unique:school_classes,class_name';
        }

        return $rules;
    }
}
