<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\AddColumn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddColumn::class)]
class AddColumnTest extends TestCase
{
    #[TestWith(['id', 'int', false, false, false, null, '`id` int NOT NULL'])]
    #[TestWith(['id', 'int', true, false, false, null, '`id` int NULL DEFAULT NULL'])]
    #[TestWith(['id', 'int', false, true, false, null, '`id` int NOT NULL AUTO_INCREMENT'])]
    #[TestWith(['id', 'int', false, false, true, null, '`id` int NOT NULL PRIMARY KEY'])]
    #[TestWith(['id', 'int', false, false, false, 'one', '`id` int NOT NULL DEFAULT "one"'])]
    #[TestWith(['id', 'int', false, false, false, 1, '`id` int NOT NULL DEFAULT "1"'])]
    #[TestWith(['id', 'int', false, false, false, 1.1, '`id` int NOT NULL DEFAULT "1.1"'])]
    #[TestWith(['id', 'int', true, true, true, 'default', '`id` int NULL AUTO_INCREMENT PRIMARY KEY DEFAULT "default"'])]
    public function testGetQuery(
        string $name,
        string $type,
        bool $nullable,
        bool $autoincrement,
        bool $primary,
        string|int|float|null $default,
        string $expected,
    ): void
    {
        $query = new AddColumn($name, $type, $nullable, $autoincrement, $primary, $default);

        self::assertSame($expected, $query->getQuery());
    }
}
