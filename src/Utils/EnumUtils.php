<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Utils;

use BackedEnum;

class EnumUtils
{
    /**
     * @param class-string<BackedEnum> $enumClass
     * @return list<string>
     */
    public static function getEnumValues(string $enumClass): array
    {
        return array_map(
            fn($case) => (string) $case,
            array_column($enumClass::cases(), 'value'),
        );
    }
}
