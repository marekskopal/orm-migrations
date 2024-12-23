<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

readonly class DatabaseSchema
{
    /** @param array<string, TableSchema> $tables */
    public function __construct(public array $tables)
    {
    }
}
