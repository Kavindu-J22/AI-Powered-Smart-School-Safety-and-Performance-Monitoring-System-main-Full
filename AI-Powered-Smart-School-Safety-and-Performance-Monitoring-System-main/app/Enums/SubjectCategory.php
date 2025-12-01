<?php

namespace App\Enums;

enum SubjectCategory: string
{
    case CORE = 'Core';
    case FIRST_LANGUAGE = 'First Language';
    case RELIGION = 'Religion';
    case AESTHETIC_STUDIES = 'Aesthetic Studies';
    case ELECTIVE = 'Elective';
    case ARTS_STREAM = 'Arts Stream';
    case COMMERCE_STREAM = 'Commerce Stream';
    case SCIENCE_STREAM = 'Science Stream';
    case TECHNOLOGY_STREAM = 'Technology Stream';

    /**
     * Get label for display
     */
    public function getLabel(): string
    {
        return $this->value;
    }

    /**
     * Check if category requires selection (student must choose one)
     */
    public function requiresSelection(): bool
    {
        return match ($this) {
            self::FIRST_LANGUAGE, self::RELIGION => true,
            default => false,
        };
    }

    /**
     * Check if category is a stream subject
     */
    public function isStream(): bool
    {
        return match ($this) {
            self::ARTS_STREAM, self::COMMERCE_STREAM,
            self::SCIENCE_STREAM, self::TECHNOLOGY_STREAM => true,
            default => false,
        };
    }

    /**
     * Get all stream categories
     */
    public static function getStreamCategories(): array
    {
        return [
            self::ARTS_STREAM,
            self::COMMERCE_STREAM,
            self::SCIENCE_STREAM,
            self::TECHNOLOGY_STREAM,
        ];
    }

    /**
     * Get primary education categories
     */
    public static function getPrimaryCategories(): array
    {
        return [
            self::FIRST_LANGUAGE,
            self::CORE,
            self::RELIGION,
            self::AESTHETIC_STUDIES,
        ];
    }

    /**
     * Get secondary education categories
     */
    public static function getSecondaryCategories(): array
    {
        return [
            self::FIRST_LANGUAGE,
            self::CORE,
            self::RELIGION,
            self::ELECTIVE,
        ];
    }

    /**
     * Get all category options
     */
    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($category) {
            return [$category->value => $category->getLabel()];
        })->toArray();
    }
}
