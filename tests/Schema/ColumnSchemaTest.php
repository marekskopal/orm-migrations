<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Schema;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Utils\ArrayUtils;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ColumnSchema::class)]
#[UsesClass(ArrayUtils::class)]
#[UsesClass(StringUtils::class)]
final class ColumnSchemaTest extends TestCase
{
    public function testEquals(): void
    {
        $columnSchema = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        $columnSchema2 = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        self::assertTrue($columnSchema->equals($columnSchema2));
    }

    public function testEqualsWithDifferentNames(): void
    {
        $columnSchema = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        $columnSchema2 = new ColumnSchema(
            name: 'cost',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        self::assertFalse($columnSchema->equals($columnSchema2));
    }

    public function testEqualsWithDifferentTypes(): void
    {
        $columnSchema = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        $columnSchema2 = new ColumnSchema(
            name: 'price',
            type: Type::Int,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        self::assertFalse($columnSchema->equals($columnSchema2));
    }

    public function testEqualsWithNullSize(): void
    {
        $columnSchema = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        $columnSchema2 = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: null,
            precision: 2,
            scale: 0,
            enum: null,
            default: null,
        );

        self::assertTrue($columnSchema->equals($columnSchema2));
        self::assertFalse($columnSchema2->equals($columnSchema));
    }

    public function testEqualsWithDifferentDefaults(): void
    {
        $columnSchema = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: 1.1,
        );

        $columnSchema2 = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: '1.1',
        );

        self::assertTrue($columnSchema->equals($columnSchema2));

        $columnSchema = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: false,
        );

        $columnSchema2 = new ColumnSchema(
            name: 'price',
            type: Type::Decimal,
            nullable: false,
            autoincrement: false,
            primary: false,
            size: 10,
            precision: 2,
            scale: 0,
            enum: null,
            default: '0',
        );

        self::assertTrue($columnSchema->equals($columnSchema2));
    }
}
