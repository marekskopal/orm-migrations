<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Utils;

final class EscapeUtils
{
    public static function escape(string $name, string $quoteChar = '`'): string
    {
        return $quoteChar . $name . $quoteChar;
    }
}
