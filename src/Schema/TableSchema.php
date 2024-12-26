<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

readonly class TableSchema
{
    /**
     * @param array<string, ColumnSchema> $columns
     * @param array<string, IndexSchema> $indexes
     * @param array<string, ForeignKeySchema> $foreignKeys
     */
    public function __construct(public string $name, public array $columns, public array $indexes, public array $foreignKeys)
    {
    }
}
