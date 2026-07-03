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

    /**
     * Escapes the contents of a string literal (without adding the surrounding quotes).
     *
     * @param bool $escapeBackslash Whether a backslash is an escape character in the
     *     dialect's string literals (true for MySQL, false for standard-conforming
     *     PostgreSQL strings).
     */
    public static function escapeStringLiteral(string $value, string $quoteChar, bool $escapeBackslash = false): string
    {
        if ($escapeBackslash) {
            $value = str_replace('\\', '\\\\', $value);
        }

        return str_replace($quoteChar, $quoteChar . $quoteChar, $value);
    }
}
