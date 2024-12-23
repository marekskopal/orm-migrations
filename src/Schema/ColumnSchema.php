<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

use BackedEnum;

readonly class ColumnSchema
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable,
        public bool $autoincrement,
        public bool $primary,
        public string|int|float|bool|BackedEnum|null $default,
    )
    {
    }
}
