<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultForeignKey;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultIndex;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultTable;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
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

            $tablesToDrop[] = new CompareResultTable($tableDatabase->name, [], [], [], [], [], [], []);
        }

        foreach ($schemaOrm->tables as $tableOrm) {
            $tableDatabase = $schemaDatabase->tables[$tableOrm->name] ?? null;

            if ($tableDatabase === null) {
                $tablesToCreate[] = new CompareResultTable(
                    name: $tableOrm->name,
                    columnsToCreate: array_values(array_map(
                        fn (ColumnSchema $column) => CompareResultColumn::fromColumnSchema($column),
                        $tableOrm->columns,
                    )),
                    columnsToDrop: [],
                    columnsToAlter: [],
                    indexesToCreate: array_values(array_map(
                        fn (IndexSchema $index) => CompareResultIndex::fromIndexSchema($index),
                        $tableOrm->indexes,
                    )),
                    indexesToDrop: [],
                    foreignKeysToCreate: array_values(array_map(
                        fn (ForeignKeySchema $foreignKey) => CompareResultForeignKey::fromForeignKeySchema($foreignKey),
                        $tableOrm->foreignKeys,
                    )),
                    foreignKeysToDrop: [],
                );
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

        usort($tablesToCreate, fn (CompareResultTable $a, CompareResultTable $b): int => $this->sortTablesToCreate($a, $b));

        return new CompareResult($tablesToCreate, $tablesToDrop, $tablesToAlter);
    }

    private function sortTablesToCreate(CompareResultTable $a, CompareResultTable $b): int
    {
        if (count($a->foreignKeysToCreate) === 0 && count($b->foreignKeysToCreate) === 0) {
            return 0;
        }

        if (count($a->foreignKeysToCreate) === 0) {
            return -1;
        }

        if (count($b->foreignKeysToCreate) === 0) {
            return 1;
        }

        foreach ($a->foreignKeysToCreate as $foreignKeyA) {
            if ($foreignKeyA->referenceTable === $b->name) {
                return 1;
            }
        }

        foreach ($b->foreignKeysToCreate as $foreignKeyB) {
            if ($foreignKeyB->referenceTable === $a->name) {
                return -1;
            }
        }

        return 0;
    }

    private function compareTable(TableSchema $tableDatabase, TableSchema $tableOrm): CompareResultTable
    {
        return new CompareResultTable(
            name: $tableOrm->name,
            columnsToCreate: $this->compareTableColumnToCreate($tableDatabase, $tableOrm),
            columnsToDrop: $this->compareTableColumnToDrop($tableDatabase, $tableOrm),
            columnsToAlter: $this->compareTableColumnToAlter($tableDatabase, $tableOrm),
            indexesToCreate: $this->compareTableIndexToCreate($tableDatabase, $tableOrm),
            indexesToDrop: $this->compareTableIndexToDrop($tableDatabase, $tableOrm),
            foreignKeysToCreate: $this->compareTableForeignKeyToCreate($tableDatabase, $tableOrm),
            foreignKeysToDrop: $this->compareTableForeignKeyToDrop($tableDatabase, $tableOrm),
        );
    }

    /** @return list<CompareResultColumn> */
    private function compareTableColumnToDrop(TableSchema $tableDatabase, TableSchema $tableOrm): array
    {
        $columnsToDrop = [];

        foreach ($tableDatabase->columns as $columnDatabase) {
            $columnOrm = $tableOrm->columns[$columnDatabase->name] ?? null;

            if ($columnOrm !== null) {
                continue;
            }

            $columnsToDrop[] = CompareResultColumn::fromColumnSchema($columnDatabase);
        }

        return $columnsToDrop;
    }

    /** @return list<CompareResultColumn> */
    private function compareTableColumnToCreate(TableSchema $tableDatabase, TableSchema $tableOrm): array
    {
        $columnsToCreate = [];

        foreach ($tableOrm->columns as $columnOrm) {
            $columnDatabase = $tableDatabase->columns[$columnOrm->name] ?? null;

            if ($columnDatabase === null) {
                $columnsToCreate[] = CompareResultColumn::fromColumnSchema($columnOrm);
            }
        }

        return $columnsToCreate;
    }

    /** @return list<CompareResultColumn> */
    private function compareTableColumnToAlter(TableSchema $tableDatabase, TableSchema $tableOrm): array
    {
        $columnsToAlter = [];

        foreach ($tableOrm->columns as $columnOrm) {
            $columnDatabase = $tableDatabase->columns[$columnOrm->name] ?? null;

            if ($columnDatabase === null) {
                continue;
            }

            $compareResultColumn = $this->compareColumn($columnDatabase, $columnOrm);
            if ($compareResultColumn === null) {
                continue;
            }

            $columnsToAlter[] = $compareResultColumn;
        }

        return $columnsToAlter;
    }

    /** @return list<CompareResultIndex> */
    private function compareTableIndexToCreate(TableSchema $tableDatabase, TableSchema $tableOrm): array
    {
        $indexesToCreate = [];

        foreach ($tableOrm->indexes as $indexOrm) {
            $indexDatabase = $tableDatabase->indexes[$indexOrm->name] ?? null;

            if ($indexDatabase === null) {
                $indexesToCreate[] = CompareResultIndex::fromIndexSchema($indexOrm);
            }
        }

        return $indexesToCreate;
    }

    /** @return list<CompareResultIndex> */
    private function compareTableIndexToDrop(TableSchema $tableDatabase, TableSchema $tableOrm): array
    {
        $indexesToDrop = [];

        foreach ($tableDatabase->indexes as $indexDatabase) {
            $indexOrm = $tableOrm->indexes[$indexDatabase->name] ?? null;

            if ($indexOrm !== null) {
                continue;
            }

            $indexesToDrop[] = CompareResultIndex::fromIndexSchema($indexDatabase);
        }

        return $indexesToDrop;
    }

    /** @return list<CompareResultForeignKey> */
    private function compareTableForeignKeyToCreate(TableSchema $tableDatabase, TableSchema $tableOrm): array
    {
        $foreignKeysToCreate = [];

        foreach ($tableOrm->foreignKeys as $foreignKeyOrm) {
            $foreignKeyDatabase = $tableDatabase->foreignKeys[$foreignKeyOrm->name] ?? null;

            if ($foreignKeyDatabase === null) {
                $foreignKeysToCreate[] = CompareResultForeignKey::fromForeignKeySchema($foreignKeyOrm);
            }
        }

        return $foreignKeysToCreate;
    }

    /** @return list<CompareResultForeignKey> */
    private function compareTableForeignKeyToDrop(TableSchema $tableDatabase, TableSchema $tableOrm): array
    {
        $foreignKeysToDrop = [];

        foreach ($tableDatabase->foreignKeys as $foreignKeyDatabase) {
            $foreignKeyOrm = $tableOrm->foreignKeys[$foreignKeyDatabase->name] ?? null;

            if ($foreignKeyOrm !== null) {
                continue;
            }

            $foreignKeysToDrop[] = CompareResultForeignKey::fromForeignKeySchema($foreignKeyDatabase);
        }

        return $foreignKeysToDrop;
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
