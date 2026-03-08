<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlInsert;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlInsert::class)]
#[UsesClass(StringUtils::class)]
final class MySqlInsertTest extends TestCase
{
    public function testGetQuery(): void
    {
        $insert = new MySqlInsert('users', [
            ['id' => 1, 'name' => 'John', 'surname' => 'Doe'],
            ['id' => 2, 'name' => 'Jane', 'surname' => 'Doe'],
        ]);

        self::assertSame(
            'INSERT INTO `users` (`id`, `name`, `surname`) VALUES (1, "John", "Doe"), (2, "Jane", "Doe");',
            $insert->getQuery(),
        );
    }
}
