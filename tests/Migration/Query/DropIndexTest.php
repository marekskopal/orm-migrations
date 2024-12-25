<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\DropIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DropIndex::class)]
final class DropIndexTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new DropIndex('index_users_address_id');

        self::assertSame('DROP INDEX `index_users_address_id`', $query->getQuery());
    }
}
