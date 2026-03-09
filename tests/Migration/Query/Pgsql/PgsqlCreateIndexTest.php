<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlCreateIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgsqlCreateIndex::class)]
final class PgsqlCreateIndexTest extends TestCase
{
    /** @param list<string> $columns */
    #[TestWith(
        [['address_id'], 'index_users_address_id', false, 'users', 'CREATE INDEX "index_users_address_id" ON "users" ("address_id");'],
    )]
    #[TestWith(
        [['address_id', 'name'], 'index_users_address_id_name', false, 'users', 'CREATE INDEX "index_users_address_id_name" ON "users" ("address_id", "name");'],
    )]
    #[TestWith(
        [['address_id'], 'index_users_address_id', true, 'users', 'CREATE UNIQUE INDEX "index_users_address_id" ON "users" ("address_id");'],
    )]
    public function testGetQuery(array $columns, string $name, bool $unique, string $tableName, string $expected): void
    {
        $query = new PgsqlCreateIndex($columns, $name, $unique, $tableName);

        self::assertSame($expected, $query->getQuery());
    }
}
