<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Fixtures;

use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;

final class ForeignKeySchemaFixture
{
    public static function create(
        ?string $column = null,
        ?string $referenceTable = null,
        ?string $referenceColumn = null,
        ?string $name = null,
        ?ReferenceOptionEnum $onDelete = null,
        ?ReferenceOptionEnum $onUpdate = null,
    ): ForeignKeySchema {
        return new ForeignKeySchema(
            column: $column ?? 'table_a_id',
            referenceTable: $referenceTable ?? 'table_a',
            referenceColumn: $referenceColumn ?? 'id',
            name: $name,
            onDelete: $onDelete ?? ReferenceOptionEnum::Cascade,
            onUpdate: $onUpdate ?? ReferenceOptionEnum::Cascade,
        );
    }
}
