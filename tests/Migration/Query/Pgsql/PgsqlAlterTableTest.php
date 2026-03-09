<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlAddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlAddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlAlterColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlAlterTable;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlDropColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlDropForeignKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgsqlAlterTable::class)]
#[UsesClass(PgsqlAddColumn::class)]
#[UsesClass(PgsqlDropColumn::class)]
#[UsesClass(PgsqlAlterColumn::class)]
#[UsesClass(PgsqlAddForeignKey::class)]
#[UsesClass(PgsqlDropForeignKey::class)]
final class PgsqlAlterTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new PgsqlAlterTable(
            'table',
            [
                new PgsqlAddColumn('name', 'varchar', false, false, false, 255),
            ],
            [
                new PgsqlDropColumn('column_a'),
            ],
            [
                new PgsqlAlterColumn('column_b', 'integer'),
            ],
            [
                new PgsqlAddForeignKey('fk_b', 'column_a', 'table_a'),
            ],
            [
                new PgsqlDropForeignKey('fk_c'),
            ],
        );

        self::assertSame(
            'ALTER TABLE "table" ADD "name" VARCHAR(255) NOT NULL, DROP COLUMN "column_a", ALTER COLUMN "column_b" TYPE INTEGER, ALTER COLUMN "column_b" SET NOT NULL, ADD FOREIGN KEY ("fk_b") REFERENCES "column_a"("table_a") ON DELETE CASCADE ON UPDATE CASCADE, DROP CONSTRAINT "fk_c";',
            $query->getQuery(),
        );
    }

    public function testIsEmpty(): void
    {
        $query = new PgsqlAlterTable('table');

        self::assertTrue($query->isEmpty());
    }

    public function testIsNotEmpty(): void
    {
        $query = new PgsqlAlterTable('table', [new PgsqlAddColumn('name', 'varchar', false, false, false, 255)]);

        self::assertFalse($query->isEmpty());
    }
}
