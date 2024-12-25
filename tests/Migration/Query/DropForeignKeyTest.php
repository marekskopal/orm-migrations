<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\DropForeignKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DropForeignKey::class)]
final class DropForeignKeyTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new DropForeignKey('fk_users_address_id');

        self::assertSame('DROP FOREIGN KEY `fk_users_address_id`', $query->getQuery());
    }
}
