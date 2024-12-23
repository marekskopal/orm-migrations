<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

use BackedEnum;

readonly class ColumnSchema
{
    /** @param list<string>|null $enum */
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable,
        public bool $autoincrement,
        public bool $primary,
        public ?int $size,
        public ?int $precision,
        public ?int $scale,
        public ?array $enum,
        public string|int|float|bool|BackedEnum|null $default,
    ) {
    }
}
