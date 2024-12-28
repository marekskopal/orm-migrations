<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\AddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\AddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\AddIndex;
use MarekSkopal\ORM\Migrations\Migration\Query\AlterColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\AlterTable;
use MarekSkopal\ORM\Migrations\Migration\Query\DropColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\DropForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\DropIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlterTable::class)]
#[UsesClass(AddColumn::class)]
#[UsesClass(DropColumn::class)]
#[UsesClass(AlterColumn::class)]
#[UsesClass(AddIndex::class)]
#[UsesClass(DropIndex::class)]
#[UsesClass(AddForeignKey::class)]
#[UsesClass(DropForeignKey::class)]
final class AlterTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new AlterTable(
            'table',
            [
                new AddColumn(
                    'name',
                    'varchar',
                    false,
                    false,
                    false,
                    255,
                ),
            ],
            [
                new DropColumn('column_a'),
            ],
            [
                new AlterColumn(
                    'column_b',
                    'int',
                ),
            ],
            [
                new AddIndex(
                    ['column_a'],
                    'index_a',
                    false,
                ),
            ],
            [
                new DropIndex(
                    'fk_a',
                ),
            ],
            [
                new AddForeignKey(
                    'fk_b',
                    'column_a',
                    'table_a',
                ),
            ],
            [
                new DropForeignKey(
                    'fk_c',
                ),
            ],
        );

        self::assertSame(
            'ALTER TABLE `table` ADD `name` VARCHAR(255) NOT NULL, DROP COLUMN `column_a`, CHANGE `column_b` `column_b` INT NOT NULL, ADD INDEX `index_a` (`column_a`), DROP INDEX `fk_a`, ADD FOREIGN KEY (`fk_b`) REFERENCES `column_a`(`table_a`) ON DELETE CASCADE ON UPDATE CASCADE, DROP FOREIGN KEY `fk_c`;',
            $query->getQuery(),
        );
    }
}
