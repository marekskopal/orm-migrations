<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Utils;

use BackedEnum;

class StringUtils
{
    /** @param string|int|float|bool|BackedEnum|array<string>|null $value */
    public static function toCode(string|int|float|bool|BackedEnum|array|null $value): string
    {
        return match (true) {
            is_array($value) => '[' . implode(', ', array_map(fn ($v) => self::toCode($v), $value)) . ']',
            is_string($value) => '\'' . $value . '\'',
            is_int($value) => (string) $value,
            is_float($value) => (string) $value,
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'null',
            $value instanceof BackedEnum => '\'' . $value->value . '\'',
        };
    }

    /** @param string|int|float|bool|BackedEnum|array<string>|null $value */
    public static function toCompare(string|int|float|bool|BackedEnum|array|null $value): string
    {
        return match (true) {
            is_array($value) => '[' . implode(', ', array_map(fn ($v) => self::toCompare($v), $value)) . ']',
            is_string($value) => $value,
            is_int($value) => (string) $value,
            is_float($value) => (string) $value,
            is_bool($value) => $value ? '1' : '0',
            is_null($value) => 'null',
            $value instanceof BackedEnum => (string) $value->value,
        };
    }

    public static function toSql(string|int|float|bool|BackedEnum|null $value): string
    {
        return match (true) {
            is_string($value) => '"' . $value . '"',
            is_int($value) => (string) $value,
            is_float($value) => (string) $value,
            is_bool($value) => $value ? '1' : '0',
            is_null($value) => 'null',
            $value instanceof BackedEnum => (string) $value->value,
        };
    }
}
