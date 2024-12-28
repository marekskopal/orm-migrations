<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;

readonly class CompareResultForeignKey
{
    public function __construct(public ForeignKeySchema $changedForeignKey, public ?ForeignKeySchema $originalForeignKey,)
    {
    }

    public static function fromForeignKeySchema(ForeignKeySchema $changedForeignKey, ?ForeignKeySchema $originalForeignKey = null): self
    {
        return new self(changedForeignKey: $changedForeignKey, originalForeignKey: $originalForeignKey);
    }
}
