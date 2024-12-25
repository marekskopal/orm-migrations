<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\AddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddForeignKey::class)]
final class AddForeignKeyTest extends TestCase
{
    #[TestWith(
        ['address_id', 'addresses', 'id', 'fk_users_address_id', ReferenceOptionEnum::Cascade, ReferenceOptionEnum::Cascade, 'CONSTRAINT `fk_users_address_id` FOREIGN KEY (`address_id`) REFERENCES `addresses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    )]
    #[TestWith(
        ['address_id', 'addresses', 'id', null, ReferenceOptionEnum::Cascade, ReferenceOptionEnum::Cascade, 'FOREIGN KEY (`address_id`) REFERENCES `addresses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE'],
    )]
    public function testGetQuery(
        string $column,
        string $referenceTable,
        string $referenceColumn,
        ?string $name,
        ReferenceOptionEnum $onDelete,
        ReferenceOptionEnum $onUpdate,
        string $expected,
    ): void {
        $query = new AddForeignKey($column, $referenceTable, $referenceColumn, $name, $onDelete, $onUpdate);

        self::assertSame($expected, $query->getQuery());
    }
}
