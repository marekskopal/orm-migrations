<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Utils;

final class EscapeUtils
{
    public static function escape(string $name, string $quoteChar = '`'): string
    {
        // Both MySQL (backtick) and ANSI/PostgreSQL (double quote) escape the
        // delimiter inside an identifier by doubling it.
        return $quoteChar . str_replace($quoteChar, $quoteChar . $quoteChar, $name) . $quoteChar;
    }
}
