<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddIndex;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlCreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlCreateTable::class)]
#[UsesClass(MySqlAddColumn::class)]
#[UsesClass(MySqlAddIndex::class)]
#[UsesClass(MySqlAddForeignKey::class)]
final class MySqlCreateTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new MySqlCreateTable(
            'table',
            [
                new MySqlAddColumn('name', 'varchar', false, false, false, 255),
                new MySqlAddColumn('table_a', 'int', false, false, false, 11),
                new MySqlAddColumn('is_active', 'tinyint', false, false, false, 1, default: true),
            ],
            [],
            [],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL, `is_active` TINYINT(1) NOT NULL DEFAULT "1");',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithIndex(): void
    {
        $query = new MySqlCreateTable(
            'table',
            [
                new MySqlAddColumn('name', 'varchar', false, false, false, 255),
                new MySqlAddColumn('table_a', 'int', false, false, false, 11),
            ],
            [new MySqlAddIndex(['name'], 'name_index', false)],
            [],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL, INDEX `name_index` (`name`));',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithForeignKey(): void
    {
        $query = new MySqlCreateTable(
            'table',
            [
                new MySqlAddColumn('name', 'varchar', false, false, false, 255),
                new MySqlAddColumn('table_a', 'int', false, false, false, 11),
            ],
            [],
            [new MySqlAddForeignKey('table_a', 'table_a', 'id', 'table_a_id_fk')],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL, CONSTRAINT `table_a_id_fk` FOREIGN KEY (`table_a`) REFERENCES `table_a`(`id`) ON DELETE CASCADE ON UPDATE CASCADE);',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithIndexAnForeignKey(): void
    {
        $query = new MySqlCreateTable(
            'table',
            [
                new MySqlAddColumn('name', 'varchar', false, false, false, 255),
                new MySqlAddColumn('table_a', 'int', false, false, false, 11),
            ],
            [new MySqlAddIndex(['name'], 'name_index', false)],
            [new MySqlAddForeignKey('table_a', 'table_a', 'id', 'table_a_id_fk')],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL, INDEX `name_index` (`name`), CONSTRAINT `table_a_id_fk` FOREIGN KEY (`table_a`) REFERENCES `table_a`(`id`) ON DELETE CASCADE ON UPDATE CASCADE);',
            $query->getQuery(),
        );
    }
}
