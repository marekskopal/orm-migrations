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
        ['users', ['address_id'], 'index_users_address_id', false, 'CREATE INDEX `index_users_address_id` ON `users` (`address_id`)'],
    )]
    #[TestWith(
        ['users', ['address_id', 'name'], 'index_users_address_id_name', false, 'CREATE INDEX `index_users_address_id_name` ON `users` (`address_id`, `name`)'],
    )]
    #[TestWith(
        ['users', ['address_id'], 'index_users_address_id', true, 'CREATE UNIQUE INDEX `index_users_address_id` ON `users` (`address_id`)'],
    )]
    public function testGetQuery(string $table, array $columns, string $name, bool $unique, string $expected): void
    {
        $query = new AddIndex($table, $columns, $name, $unique);

        self::assertSame($expected, $query->getQuery());
    }
}
