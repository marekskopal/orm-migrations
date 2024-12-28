<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

use MarekSkopal\ORM\Migrations\Schema\IndexSchema;

readonly class CompareResultIndex
{
    public function __construct(public IndexSchema $changedIndex, public ?IndexSchema $originalIndex)
    {
    }

    public static function fromIndexSchema(IndexSchema $changedIndex, ?IndexSchema $originalIndex = null): self
    {
        return new self(changedIndex: $changedIndex, originalIndex: $originalIndex);
    }
}
