<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlDropColumn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlDropColumn::class)]
final class MySqlDropColumnTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new MySqlDropColumn('column');

        self::assertSame('DROP COLUMN `column`', $query->getQuery());
    }
}
