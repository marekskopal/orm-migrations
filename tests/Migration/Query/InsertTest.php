<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\Insert;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Insert::class)]
#[UsesClass(StringUtils::class)]
final class InsertTest extends TestCase
{
    public function testGetQuery(): void
    {
        $insert = new Insert('users', [
            ['id' => 1, 'name' => 'John', 'surname' => 'Doe'],
            ['id' => 2, 'name' => 'Jane', 'surname' => 'Doe'],
        ]);

        self::assertSame(
            'INSERT INTO TABLE `users` (`id`, `name`, `surname`) VALUES (1, "John", "Doe"), (2, "Jane", "Doe");',
            $insert->getQuery(),
        );
    }
}
