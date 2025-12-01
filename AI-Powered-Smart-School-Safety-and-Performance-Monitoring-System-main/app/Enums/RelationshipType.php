<?php

namespace App\Enums;

enum RelationshipType: string
{
    case FATHER = 'Father';
    case MOTHER = 'Mother';
    case GUARDIAN = 'Guardian';
    case STEPFATHER = 'Stepfather';
    case STEPMOTHER = 'Stepmother';
    case GRANDFATHER = 'Grandfather';
    case GRANDMOTHER = 'Grandmother';
    case UNCLE = 'Uncle';
    case AUNT = 'Aunt';
    case OTHER = 'Other';

    public static function getValues(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }

    public static function getValidationRule(): string
    {
        return 'in:'.implode(',', self::getValues());
    }

    public function getLabel(): string
    {
        return $this->value;
    }
}
