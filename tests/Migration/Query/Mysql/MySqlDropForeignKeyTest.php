<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlDropForeignKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlDropForeignKey::class)]
final class MySqlDropForeignKeyTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new MySqlDropForeignKey('fk_users_address_id');

        self::assertSame('DROP FOREIGN KEY `fk_users_address_id`', $query->getQuery());
    }
}
