<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\DropColumn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DropColumn::class)]
final class DropColumnTest extends TestCase
{
    public function testGetQuery(): void
    {
        $query = new DropColumn('column');

        self::assertSame('DROP COLUMN `column`', $query->getQuery());
    }
}
