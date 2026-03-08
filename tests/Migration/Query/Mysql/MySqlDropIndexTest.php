<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlDropIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlDropIndex::class)]
final class MySqlDropIndexTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new MySqlDropIndex('index_users_address_id');

        self::assertSame('DROP INDEX `index_users_address_id`', $query->getQuery());
    }
}
