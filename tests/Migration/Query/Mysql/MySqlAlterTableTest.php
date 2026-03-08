<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddIndex;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAlterColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAlterTable;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlDropColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlDropForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlDropIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlAlterTable::class)]
#[UsesClass(MySqlAddColumn::class)]
#[UsesClass(MySqlDropColumn::class)]
#[UsesClass(MySqlAlterColumn::class)]
#[UsesClass(MySqlAddIndex::class)]
#[UsesClass(MySqlDropIndex::class)]
#[UsesClass(MySqlAddForeignKey::class)]
#[UsesClass(MySqlDropForeignKey::class)]
final class MySqlAlterTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new MySqlAlterTable(
            'table',
            [
                new MySqlAddColumn(
                    'name',
                    'varchar',
                    false,
                    false,
                    false,
                    255,
                ),
            ],
            [
                new MySqlDropColumn('column_a'),
            ],
            [
                new MySqlAlterColumn(
                    'column_b',
                    'int',
                ),
            ],
            [
                new MySqlAddIndex(
                    ['column_a'],
                    'index_a',
                    false,
                ),
            ],
            [
                new MySqlDropIndex(
                    'fk_a',
                ),
            ],
            [
                new MySqlAddForeignKey(
                    'fk_b',
                    'column_a',
                    'table_a',
                ),
            ],
            [
                new MySqlDropForeignKey(
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
