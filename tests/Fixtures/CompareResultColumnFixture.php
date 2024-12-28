<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Fixtures;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;

final readonly class CompareResultColumnFixture
{
    public static function create(?ColumnSchema $changedColumn = null, ?ColumnSchema $originalColumn = null,): CompareResultColumn
    {
        return new CompareResultColumn(
            changedColumn: $changedColumn ?? ColumnSchemaFixture::create(),
            originalColumn: $originalColumn,
        );
    }
}
