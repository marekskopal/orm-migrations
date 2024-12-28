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
use MarekSkopal\ORM\Migrations\Utils\ArrayUtils;

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

            $tablesToDrop[] = new CompareResultTable(
                name: $tableDatabase->name,
                columnsToCreate: [],
                columnsToDrop: array_values(array_map(
                    fn (ColumnSchema $column) => CompareResultColumn::fromColumnSchema($column, $column),
                    $tableDatabase->columns,
                )),
                columnsToAlter: [],
                indexesToCreate: [],
                indexesToDrop: [],
                foreignKeysToCreate: [],
                foreignKeysToDrop: [],
            );
        }

        foreach ($schemaOrm->tables as $tableOrm) {
            $tableDatabase = $schemaDatabase->tables[$tableOrm->name] ?? null;

            if ($tableDatabase === null) {
                $columnsToCreate = array_values(array_map(
                    fn (ColumnSchema $column) => CompareResultColumn::fromColumnSchema($column),
                    $tableOrm->columns,
                ));
                usort(
                    $columnsToCreate,
                    fn (CompareResultColumn $a, CompareResultColumn $b) => !$a->changedColumn->primary <=> !$b->changedColumn->primary,
                );

                $tablesToCreate[] = new CompareResultTable(
                    name: $tableOrm->name,
                    columnsToCreate: $columnsToCreate,
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

        $tablesToCreate = $this->sortTablesToCreate($tablesToCreate);

        return new CompareResult($tablesToCreate, $tablesToDrop, $tablesToAlter);
    }

    /**
     * @param list<CompareResultTable> $tablesToCreate
     * @return list<CompareResultTable>
     */
    private function sortTablesToCreate(array $tablesToCreate): array
    {
        $sortedTablesToCreate = [];

        foreach ($tablesToCreate as $key => $tableToCreate) {
            if (count($tableToCreate->foreignKeysToCreate) === 0) {
                $sortedTablesToCreate[$tableToCreate->name] = $tableToCreate;
                unset($tablesToCreate[$key]);
            }
        }

        foreach ($tablesToCreate as $key => $tableToCreate) {
            $hasOnlyForeignKeysToSelf = true;
            $foreignKeysToCreate = $tableToCreate->foreignKeysToCreate;
            foreach ($foreignKeysToCreate as $foreignKeyToCreate) {
                if ($foreignKeyToCreate->changedForeignKey->referenceTable !== $tableToCreate->name) {
                    $hasOnlyForeignKeysToSelf = false;
                    break;
                }
            }
            if (!$hasOnlyForeignKeysToSelf) {
                continue;
            }

            $sortedTablesToCreate[$tableToCreate->name] = $tableToCreate;
            unset($tablesToCreate[$key]);
        }

        $maxIterations = count($tablesToCreate);
        for ($i = 0; count($tablesToCreate) > 0 && $i < $maxIterations; $i++) {
            foreach ($tablesToCreate as $key => $tableToCreate) {
                $hasAllForeignKeysTablesCreated = true;
                $foreignKeysToCreate = $tableToCreate->foreignKeysToCreate;
                foreach ($foreignKeysToCreate as $foreignKeyToCreate) {
                    if (!array_key_exists($foreignKeyToCreate->changedForeignKey->referenceTable, $sortedTablesToCreate)) {
                        $hasAllForeignKeysTablesCreated = false;
                        break;
                    }
                }
                if (!$hasAllForeignKeysTablesCreated) {
                    continue;
                }

                $sortedTablesToCreate[$tableToCreate->name] = $tableToCreate;
                unset($tablesToCreate[$key]);
            }
        }

        return array_values(array_merge($sortedTablesToCreate, $tablesToCreate));
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

            $columnsToDrop[] = CompareResultColumn::fromColumnSchema($columnDatabase, $columnDatabase);
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
            // If size is null in ORM schema, it does not matter if has size in database schema
            && ($tableOrm->size === null || $tableDatabase->size === $tableOrm->size)
            && $tableDatabase->precision === $tableOrm->precision
            && $tableDatabase->scale === $tableOrm->scale
            && ArrayUtils::equals($tableDatabase->enum ?? [], $tableOrm->enum ?? [])
            && $tableDatabase->default === $tableOrm->default
        ) {
            return null;
        }

        return CompareResultColumn::fromColumnSchema($tableOrm, $tableDatabase);
    }
}
