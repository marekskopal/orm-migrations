<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;

readonly class CompareResultForeignKey
{
    public function __construct(
        public string $column,
        public string $referenceTable,
        public string $referenceColumn,
        public ?string $name = null,
        public ReferenceOptionEnum $onDelete = ReferenceOptionEnum::Cascade,
        public ReferenceOptionEnum $onUpdate = ReferenceOptionEnum::Cascade,
    ) {
    }

    public static function fromForeignKeySchema(ForeignKeySchema $foreignKey): self
    {
        return new self(
            column: $foreignKey->column,
            referenceTable: $foreignKey->referenceTable,
            referenceColumn: $foreignKey->referenceColumn,
            name: $foreignKey->name,
            onDelete: $foreignKey->onDelete,
            onUpdate: $foreignKey->onUpdate,
        );
    }
}
