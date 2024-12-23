<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultTable;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;

class SchemaComparator
{
    public function compare(DatabaseSchema $schemaDatabase, DatabaseSchema $schemaOrm): CompareResult
    {
        $tablesToCreate = [];
        $tablesToDrop = [];
        $tablesToAlter = [];

        foreach ($schemaDatabase->tables as $tableDatabase) {
            $tableOrm = $schemaOrm->tables[$tableDatabase->name] ?? null;

            if ($tableOrm !== null) {
                continue;
            }

            $tablesToDrop[] = new CompareResultTable($tableDatabase->name, [], [], []);
        }

        foreach ($schemaOrm->tables as $tableOrm) {
            $tableDatabase = $schemaDatabase->tables[$tableOrm->name] ?? null;

            if ($tableDatabase === null) {
                $tablesToCreate[] = new CompareResultTable($tableOrm->name, array_values(array_map(
                    fn (ColumnSchema $column) => CompareResultColumn::fromColumnSchema($column),
                    $tableOrm->columns,
                )), [], []);
                continue;
            }

            $compareResultTable = $this->compareTable($tableDatabase, $tableOrm);
            if (
                count($compareResultTable->columnsToCreate) === 0
                && count($compareResultTable->columnsToDrop) === 0
                && count($compareResultTable->columnsToAlter) === 0
            ) {
                continue;
            }

            $tablesToAlter[] = $compareResultTable;
        }

        return new CompareResult($tablesToCreate, $tablesToDrop, $tablesToAlter);
    }

    private function compareTable(TableSchema $tableDatabase, TableSchema $tableOrm): CompareResultTable
    {
        $columnsToCreate = [];
        $columnsToDrop = [];
        $columnsToAlter = [];

        foreach ($tableDatabase->columns as $columnDatabase) {
            $columnOrm = $tableOrm->columns[$columnDatabase->name] ?? null;

            if ($columnOrm !== null) {
                continue;
            }

            $columnsToDrop[] = CompareResultColumn::fromColumnSchema($columnDatabase);
        }

        foreach ($tableOrm->columns as $columnOrm) {
            $columnDatabase = $tableDatabase->columns[$columnOrm->name] ?? null;

            if ($columnDatabase === null) {
                $columnsToCreate[] = CompareResultColumn::fromColumnSchema($columnOrm);
                continue;
            }

            $compareResultColumn = $this->compareColumn($columnDatabase, $columnOrm);
            if ($compareResultColumn === null) {
                continue;
            }

            $columnsToAlter[] = $compareResultColumn;
        }

        return new CompareResultTable($tableOrm->name, $columnsToCreate, $columnsToDrop, $columnsToAlter);
    }

    private function compareColumn(ColumnSchema $tableDatabase, ColumnSchema $tableOrm): ?CompareResultColumn
    {
        if (
            $tableDatabase->name === $tableOrm->name
            && $tableDatabase->type === $tableOrm->type
            && $tableDatabase->nullable === $tableOrm->nullable
            && $tableDatabase->autoincrement === $tableOrm->autoincrement
            && $tableDatabase->primary === $tableOrm->primary
            && $tableDatabase->default === $tableOrm->default
        ) {
            return null;
        }

        return CompareResultColumn::fromColumnSchema($tableOrm);
    }
}
