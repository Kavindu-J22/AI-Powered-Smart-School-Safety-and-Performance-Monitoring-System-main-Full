<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

enum Grade: int
{
    use InvokableCases;
    use Names;
    use Options;
    use Values;

        // Primary Education (Grades 1-5)
    case GRADE_1 = 1;
    case GRADE_2 = 2;
    case GRADE_3 = 3;
    case GRADE_4 = 4;
    case GRADE_5 = 5;

        // Secondary Education (Grades 6-11)
    case GRADE_6 = 6;
    case GRADE_7 = 7;
    case GRADE_8 = 8;
    case GRADE_9 = 9;
    case GRADE_10 = 10;
    case GRADE_11 = 11;

        // Advanced Level (Grades 12-13)
    case GRADE_12 = 12;
    case GRADE_13 = 13;

    /**
     * Get the label for the grade
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::GRADE_1 => 'Grade 1',
            self::GRADE_2 => 'Grade 2',
            self::GRADE_3 => 'Grade 3',
            self::GRADE_4 => 'Grade 4',
            self::GRADE_5 => 'Grade 5',
            self::GRADE_6 => 'Grade 6',
            self::GRADE_7 => 'Grade 7',
            self::GRADE_8 => 'Grade 8',
            self::GRADE_9 => 'Grade 9',
            self::GRADE_10 => 'Grade 10',
            self::GRADE_11 => 'Grade 11',
            self::GRADE_12 => 'Grade 12',
            self::GRADE_13 => 'Grade 13',
        };
    }

    /**
     * Get the education level for the grade
     */
    public function getEducationLevel(): string
    {
        return match ($this) {
            self::GRADE_1, self::GRADE_2, self::GRADE_3, self::GRADE_4, self::GRADE_5
            => 'Primary Education',
            self::GRADE_6, self::GRADE_7, self::GRADE_8, self::GRADE_9, self::GRADE_10, self::GRADE_11
            => 'Secondary Education',
            self::GRADE_12, self::GRADE_13
            => 'Advanced Level',
        };
    }

    /**
     * Get the grade level range for subject filtering
     */
    public function getGradeLevel(): string
    {
        return match ($this) {
            self::GRADE_1, self::GRADE_2, self::GRADE_3, self::GRADE_4, self::GRADE_5
            => '1-5',
            self::GRADE_6, self::GRADE_7, self::GRADE_8, self::GRADE_9, self::GRADE_10, self::GRADE_11
            => '6-11',
            self::GRADE_12, self::GRADE_13
            => '12-13',
        };
    }

    /**
     * Check if grade is primary level
     */
    public function isPrimary(): bool
    {
        return $this->value >= 1 && $this->value <= 5;
    }

    /**
     * Check if grade is secondary level
     */
    public function isSecondary(): bool
    {
        return $this->value >= 6 && $this->value <= 11;
    }

    /**
     * Check if grade is advanced level
     */
    public function isAdvancedLevel(): bool
    {
        return $this->value >= 12 && $this->value <= 13;
    }

    /**
     * Get all primary grades
     */
    public static function primaryGrades(): array
    {
        return [
            self::GRADE_1,
            self::GRADE_2,
            self::GRADE_3,
            self::GRADE_4,
            self::GRADE_5,
        ];
    }

    /**
     * Get all secondary grades
     */
    public static function secondaryGrades(): array
    {
        return [
            self::GRADE_6,
            self::GRADE_7,
            self::GRADE_8,
            self::GRADE_9,
            self::GRADE_10,
            self::GRADE_11,
        ];
    }

    /**
     * Get all advanced level grades
     */
    public static function advancedLevelGrades(): array
    {
        return [
            self::GRADE_12,
            self::GRADE_13,
        ];
    }

    /**
     * Get grades as options for select dropdown
     */
    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($grade) {
            return [$grade->value => $grade->getLabel()];
        })->toArray();
    }
}
