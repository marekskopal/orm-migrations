<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

use BackedEnum;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;

readonly class CompareResultColumn
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable,
        public bool $autoincrement,
        public bool $primary,
        public ?int $size,
        public ?int $precision,
        public ?int $scale,
        public string|int|float|bool|BackedEnum|null $default,
    ) {
    }

    public static function fromColumnSchema(ColumnSchema $column): self
    {
        return new self(
            name: $column->name,
            type: $column->type,
            nullable: $column->nullable,
            autoincrement: $column->autoincrement,
            primary: $column->primary,
            size: $column->size,
            precision: $column->precision,
            scale: $column->scale,
            default: $column->default,
        );
    }
}
