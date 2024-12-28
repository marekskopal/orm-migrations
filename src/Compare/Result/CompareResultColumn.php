<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;

readonly class CompareResultColumn
{
    public function __construct(public ColumnSchema $changedColumn, public ?ColumnSchema $originalColumn,)
    {
    }

    public static function fromColumnSchema(ColumnSchema $changedColumn, ?ColumnSchema $originalColumn = null): self
    {
        return new self(changedColumn: $changedColumn, originalColumn: $originalColumn);
    }
}
