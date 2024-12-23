<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

readonly class TableSchema
{
    /** @param array<string, ColumnSchema> $columns */
    public function __construct(public string $name, public array $columns)
    {
    }
}
