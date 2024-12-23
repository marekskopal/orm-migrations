<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Utils;

use BackedEnum;

class StringUtils
{
    public static function toCode(string|int|float|bool|BackedEnum|null $value): string
    {
        return match (true) {
            is_string($value) => '\'' . $value . '\'',
            is_int($value) => (string) $value,
            is_float($value) => (string) $value,
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'null',
            $value instanceof BackedEnum => (string) $value->value,
        };
    }
}
