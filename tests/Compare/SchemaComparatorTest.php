<?php

declare(strict_types=1);

namespace Compare;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultTable;
use MarekSkopal\ORM\Migrations\Compare\SchemaComparator;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchemaComparator::class)]
#[UsesClass(CompareResult::class)]
#[UsesClass(CompareResultTable::class)]
#[UsesClass(CompareResultColumn::class)]
#[UsesClass(DatabaseSchema::class)]
#[UsesClass(TableSchema::class)]
#[UsesClass(ColumnSchema::class)]
final class SchemaComparatorTest extends TestCase
{
    public function testCompareSame(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
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
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
            'table_b' => new TableSchema('table_b', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
        ]);

        $result = $comparator->compare($schemaDatabase, $schemaOrm);

        self::assertCount(1, $result->tablesToCreate);
        self::assertCount(0, $result->tablesToDrop);
        self::assertCount(0, $result->tablesToAlter);
    }

    public function testCompareDropTable(): void
    {
        $comparator = new SchemaComparator();

        $schemaDatabase = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
            'table_b' => new TableSchema('table_b', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
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
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
                'column_c' => new ColumnSchema('column_c', 'varchar', true, false, false, null),
            ]),
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
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
            ]),
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
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'varchar', true, false, false, null),
            ]),
        ]);

        $schemaOrm = new DatabaseSchema([
            'table_a' => new TableSchema('table_a', [
                'column_a' => new ColumnSchema('column_a', 'int', false, false, false, null),
                'column_b' => new ColumnSchema('column_b', 'int', true, false, false, null),
            ]),
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
