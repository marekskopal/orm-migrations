<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlDropTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgsqlDropTable::class)]
final class PgsqlDropTableTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new PgsqlDropTable('users');

        self::assertSame('DROP TABLE "users";', $query->getQuery());
    }
}
