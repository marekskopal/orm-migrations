<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Utils;

use MarekSkopal\ORM\Migrations\Utils\ColumnType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ColumnType::class)]
class ColumnTypeTest extends TestCase
{
    /** @return list<array{0: string, 1: ColumnType}> */
    public static function parseColumnTypeDataProvider(): array
    {
        return [
            ['int', new ColumnType('int')],
            ['varchar(255)', new ColumnType('varchar', size: 255)],
            ['decimal(10,2)', new ColumnType('decimal', precision: 10, scale: 2)],
            ['enum(\'a\',\'b\',\'c\')', new ColumnType('enum', enum: ['a', 'b', 'c'])],
            ['enum("a","b","c")', new ColumnType('enum', enum: ['a', 'b', 'c'])],
        ];
    }

    #[DataProvider('parseColumnTypeDataProvider')]
    public function testParseColumnType(string $typeString, ColumnType $expectedColumnType): void
    {
        $columnType = ColumnType::parseColumnType($typeString);

        self::assertEquals($expectedColumnType, $columnType);
    }
}
