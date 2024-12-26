<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

use MarekSkopal\ORM\Migrations\Schema\IndexSchema;

readonly class CompareResultIndex
{
    /** @param list<string> $columns */
    public function __construct(public array $columns, public string $name, public bool $unique,)
    {
    }

    public static function fromIndexSchema(IndexSchema $index): self
    {
        return new self(columns: $index->columns, name: $index->name, unique: $index->unique);
    }
}
