<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlInsert;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PgsqlInsert::class)]
#[UsesClass(StringUtils::class)]
final class PgsqlInsertTest extends TestCase
{
    public function testGetQuery(): void
    {
        $insert = new PgsqlInsert('users', [
            ['id' => 1, 'name' => 'John', 'surname' => 'Doe'],
            ['id' => 2, 'name' => 'Jane', 'surname' => 'Doe'],
        ]);

        self::assertSame(
            'INSERT INTO "users" ("id", "name", "surname") VALUES (1, "John", "Doe"), (2, "Jane", "Doe");',
            $insert->getQuery(),
        );
    }
}
