<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\AddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\AddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\AddIndex;
use MarekSkopal\ORM\Migrations\Migration\Query\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateTable::class)]
#[UsesClass(AddColumn::class)]
#[UsesClass(AddIndex::class)]
#[UsesClass(AddForeignKey::class)]
final class CreateTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new CreateTable(
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
                new AddColumn(
                    'table_a',
                    'int',
                    false,
                    false,
                    false,
                    11,
                ),
            ],
            [],
            [],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL);',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithIndex(): void
    {
        $query = new CreateTable(
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
                new AddColumn(
                    'table_a',
                    'int',
                    false,
                    false,
                    false,
                    11,
                ),
            ],
            [
                new AddIndex(
                    ['name'],
                    'name_index',
                    false,
                ),
            ],
            [],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL, INDEX `name_index` (`name`));',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithForeignKey(): void
    {
        $query = new CreateTable(
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
                new AddColumn(
                    'table_a',
                    'int',
                    false,
                    false,
                    false,
                    11,
                ),
            ],
            [],
            [
                new AddForeignKey(
                    'table_a',
                    'table_a',
                    'id',
                    'table_a_id_fk',
                ),
            ],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL, CONSTRAINT `table_a_id_fk` FOREIGN KEY (`table_a`) REFERENCES `table_a`(`id`) ON DELETE CASCADE ON UPDATE CASCADE);',
            $query->getQuery(),
        );
    }

    public function testGetQueryWithIndexAnForeignKey(): void
    {
        $query = new CreateTable(
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
                new AddColumn(
                    'table_a',
                    'int',
                    false,
                    false,
                    false,
                    11,
                ),
            ],
            [
                new AddIndex(
                    ['name'],
                    'name_index',
                    false,
                ),
            ],
            [
                new AddForeignKey(
                    'table_a',
                    'table_a',
                    'id',
                    'table_a_id_fk',
                ),
            ],
        );

        self::assertSame(
            'CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL, `table_a` INT(11) NOT NULL, INDEX `name_index` (`name`), CONSTRAINT `table_a_id_fk` FOREIGN KEY (`table_a`) REFERENCES `table_a`(`id`) ON DELETE CASCADE ON UPDATE CASCADE);',
            $query->getQuery(),
        );
    }
}
