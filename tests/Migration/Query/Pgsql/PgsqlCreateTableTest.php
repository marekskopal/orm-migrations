<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlAddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlAddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlCreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgsqlCreateTable::class)]
#[UsesClass(PgsqlAddColumn::class)]
#[UsesClass(PgsqlAddForeignKey::class)]
final class PgsqlCreateTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new PgsqlCreateTable(
            'table',
            [
                new PgsqlAddColumn('name', 'varchar', false, false, false, 255),
                new PgsqlAddColumn('table_a', 'integer', false, false, false),
                new PgsqlAddColumn('is_active', 'boolean', false, false, false, default: true),
            ],
        );

        self::assertSame(
            'CREATE TABLE "table" ("name" VARCHAR(255) NOT NULL, "table_a" INTEGER NOT NULL, "is_active" BOOLEAN NOT NULL DEFAULT \'1\');',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithForeignKey(): void
    {
        $query = new PgsqlCreateTable(
            'table',
            [
                new PgsqlAddColumn('name', 'varchar', false, false, false, 255),
                new PgsqlAddColumn('table_a', 'integer', false, false, false),
            ],
            [new PgsqlAddForeignKey('table_a', 'table_a', 'id', 'table_a_id_fk')],
        );

        self::assertSame(
            'CREATE TABLE "table" ("name" VARCHAR(255) NOT NULL, "table_a" INTEGER NOT NULL, CONSTRAINT "table_a_id_fk" FOREIGN KEY ("table_a") REFERENCES "table_a"("id") ON DELETE CASCADE ON UPDATE CASCADE);',
            $query->getQuery(),
        );
    }
}
