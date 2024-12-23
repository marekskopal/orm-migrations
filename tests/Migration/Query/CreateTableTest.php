<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\AddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateTable::class)]
#[UsesClass(AddColumn::class)]
class CreateTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new CreateTable('table', [
            new AddColumn(
                'name',
                'varchar',
                false,
                false,
                false,
                255,
            ),
        ]);

        self::assertSame('CREATE TABLE `table` (`name` VARCHAR(255) NOT NULL);', $query->getQuery());
    }
}
