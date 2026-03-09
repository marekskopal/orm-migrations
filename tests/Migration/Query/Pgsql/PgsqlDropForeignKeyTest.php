<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlDropForeignKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgsqlDropForeignKey::class)]
final class PgsqlDropForeignKeyTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new PgsqlDropForeignKey('fk_users_address_id');

        self::assertSame('DROP CONSTRAINT "fk_users_address_id"', $query->getQuery());
    }
}
