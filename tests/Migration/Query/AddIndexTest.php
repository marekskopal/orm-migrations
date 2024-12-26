<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\AddIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddIndex::class)]
final class AddIndexTest extends TestCase
{
    /** @param list<string> $columns */
    #[TestWith(
        [['address_id'], 'index_users_address_id', false, 'CONSTRAINT INDEX `index_users_address_id` (`address_id`)'],
    )]
    #[TestWith(
        [['address_id', 'name'], 'index_users_address_id_name', false, 'CONSTRAINT INDEX `index_users_address_id_name` (`address_id`, `name`)'],
    )]
    #[TestWith(
        [['address_id'], 'index_users_address_id', true, 'CONSTRAINT UNIQUE INDEX `index_users_address_id` (`address_id`)'],
    )]
    public function testGetQuery(array $columns, string $name, bool $unique, string $expected): void
    {
        $query = new AddIndex($columns, $name, $unique);

        self::assertSame($expected, $query->getQuery());
    }
}
