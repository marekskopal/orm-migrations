<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Compare;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultForeignKey;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultTable;
use MarekSkopal\ORM\Migrations\Compare\SchemaComparator;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\ColumnSchemaFixture;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\ForeignKeySchemaFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaComparator::class)]
#[UsesClass(CompareResult::class)]
#[UsesClass(CompareResultTable::class)]
#[UsesClass(CompareResultColumn::class)]
#[UsesClass(CompareResultForeignKey::class)]
#[UsesClass(DatabaseSchema::class)]
#[UsesClass(TableSchema::class)]
#[UsesClass(ColumnSchema::class)]
#[UsesClass(ForeignKeySchema::class)]
final class SchemaComparatorTest extends TestCase
{
    public function testCompareSame(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $schemaOrm = clone $schemaDatabase;

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(0, $result->tablesToCreate);
        self::assertCount(0, $result->tablesToDrop);
        self::assertCount(0, $result->tablesToAlter);
    }

    public function testCompareCreateTable(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
            'table_b' => new TableSchema('table_b', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(1, $result->tablesToCreate);
        self::assertCount(0, $result->tablesToDrop);
        self::assertCount(0, $result->tablesToAlter);
    }

    public function testCompareCreateTableOrder(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([]);

        $schemaOrm = new DatabaseSchema([
            'table_b' => new TableSchema('table_b', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'table_b_id' => ColumnSchemaFixture::create(name: 'table_b_id', type: Type::Int),
            ], [], [
                'table_b_id' => ForeignKeySchemaFixture::create('table_b_id', 'table_b', 'id'),
            ]),
            'table_c' => new TableSchema('table_c', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'table_a_id' => ColumnSchemaFixture::create(name: 'table_a_id', type: Type::Int),
                'table_b_id' => ColumnSchemaFixture::create(name: 'table_b_id', type: Type::Int),
            ], [], [
                'table_a_id' => ForeignKeySchemaFixture::create(),
                'table_b_id' => ForeignKeySchemaFixture::create('table_b_id', 'table_b', 'id'),
            ]),
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
            ], [], []),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(3, $result->tablesToCreate);
        self::assertSame('table_a', $result->tablesToCreate[0]->name);
        self::assertSame('table_b', $result->tablesToCreate[1]->name);
        self::assertSame('table_c', $result->tablesToCreate[2]->name);
    }

    public function testCompareCreateTableColumnOrder(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::Int),
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int, primary: true),
            ], [], []),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(1, $result->tablesToCreate);
        self::assertCount(2, $result->tablesToCreate[0]->columnsToCreate);
        self::assertSame('column_a', $result->tablesToCreate[0]->columnsToCreate[0]->name);
    }

    public function testCompareDropTable(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
            'table_b' => new TableSchema('table_b', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(0, $result->tablesToCreate);
        self::assertCount(1, $result->tablesToDrop);
        self::assertCount(0, $result->tablesToAlter);
    }

    public function testCompareCreateColumn(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
                'column_c' => ColumnSchemaFixture::create(name: 'column_c', type: Type::String),
            ], [], []),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(0, $result->tablesToCreate);
        self::assertCount(0, $result->tablesToDrop);
        self::assertCount(1, $result->tablesToAlter);
        self::assertCount(1, $result->tablesToAlter[0]->columnsToCreate);
        self::assertCount(0, $result->tablesToAlter[0]->columnsToDrop);
        self::assertCount(0, $result->tablesToAlter[0]->columnsToAlter);
    }

    public function testCompareDropColumn(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
            ], [], []),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(0, $result->tablesToCreate);
        self::assertCount(0, $result->tablesToDrop);
        self::assertCount(1, $result->tablesToAlter);
        self::assertCount(0, $result->tablesToAlter[0]->columnsToCreate);
        self::assertCount(1, $result->tablesToAlter[0]->columnsToDrop);
        self::assertCount(0, $result->tablesToAlter[0]->columnsToAlter);
    }

    public function testCompareAlterColumn(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::String),
            ], [], []),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => ColumnSchemaFixture::create(name: 'column_a', type: Type::Int),
                'column_b' => ColumnSchemaFixture::create(name: 'column_b', type: Type::Int),
            ], [], []),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(0, $result->tablesToCreate);
        self::assertCount(0, $result->tablesToDrop);
        self::assertCount(1, $result->tablesToAlter);
        self::assertCount(0, $result->tablesToAlter[0]->columnsToCreate);
        self::assertCount(0, $result->tablesToAlter[0]->columnsToDrop);
        self::assertCount(1, $result->tablesToAlter[0]->columnsToAlter);
    }
}
