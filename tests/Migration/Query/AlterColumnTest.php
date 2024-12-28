<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\AlterColumn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlterColumn::class)]
final class AlterColumnTest extends TestCase
{
    /** @param list<string> $enum */
    #[TestWith(['id', 'int', false, false, false, null, null, null, null, null, '`id` INT NOT NULL'])]
    #[TestWith(['id', 'int', true, false, false, null, null, null, null, null, '`id` INT NULL DEFAULT NULL'])]
    #[TestWith(['id', 'int', false, true, false, null, null, null, null, null, '`id` INT NOT NULL AUTO_INCREMENT'])]
    #[TestWith(['id', 'int', false, false, true, null, null, null, null, null, '`id` INT NOT NULL PRIMARY KEY'])]
    #[TestWith(['id', 'int', false, false, false, null, null, null, null, 'one', '`id` INT NOT NULL DEFAULT "one"'])]
    #[TestWith(['id', 'int', false, false, false, 11, null, null, null, 1, '`id` INT(11) NOT NULL DEFAULT "1"'])]
    #[TestWith(['id', 'decimal', false, false, false, null, 11, 2, null, 1.1, '`id` DECIMAL(11,2) NOT NULL DEFAULT "1.1"'])]
    #[TestWith(
        ['id', 'int', true, true, true, null, null, null, null, 'default', '`id` INT NULL AUTO_INCREMENT PRIMARY KEY DEFAULT "default"'],
    )]
    #[TestWith(
        ['type', 'enum', false, false, false, null, null, null, ['a', 'b', 'c'], 'a', '`type` ENUM("a","b","c") NOT NULL DEFAULT "a"'],
    )]
    public function testGetQuery(
        string $name,
        string $type,
        bool $nullable,
        bool $autoincrement,
        bool $primary,
        ?int $size,
        ?int $precision,
        ?int $scale,
        ?array $enum,
        string|int|float|null $default,
        string $expected,
    ): void
    {
        $query = new AlterColumn($name, $type, $nullable, $autoincrement, $primary, $size, $precision, $scale, $enum, $default);

        self::assertSame($expected, $query->getQuery());
    }
}
