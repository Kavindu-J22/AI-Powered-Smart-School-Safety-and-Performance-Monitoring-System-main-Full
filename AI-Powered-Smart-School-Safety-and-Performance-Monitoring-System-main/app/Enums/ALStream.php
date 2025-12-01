<?php

namespace App\Enums;

enum ALStream: string
{
    case ARTS = 'Arts';
    case COMMERCE = 'Commerce';
    case SCIENCE = 'Science';
    case TECHNOLOGY = 'Technology';

    /**
     * Get label for display
     */
    public function getLabel(): string
    {
        return $this->value . ' Stream';
    }

    /**
     * Get the subject category for this stream
     */
    public function getSubjectCategory(): SubjectCategory
    {
        return match ($this) {
            self::ARTS => SubjectCategory::ARTS_STREAM,
            self::COMMERCE => SubjectCategory::COMMERCE_STREAM,
            self::SCIENCE => SubjectCategory::SCIENCE_STREAM,
            self::TECHNOLOGY => SubjectCategory::TECHNOLOGY_STREAM,
        };
    }

    /**
     * Get all stream options for dropdown
     */
    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($stream) {
            return [$stream->value => $stream->getLabel()];
        })->toArray();
    }
}
