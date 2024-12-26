<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;

readonly class ForeignKeySchema
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
}
