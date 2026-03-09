<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlDropIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgsqlDropIndex::class)]
final class PgsqlDropIndexTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new PgsqlDropIndex('index_users_address_id');

        self::assertSame('DROP INDEX "index_users_address_id";', $query->getQuery());
    }
}
