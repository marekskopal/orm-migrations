<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\TypeConverterInterface;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Utils\ColumnType;
use MarekSkopal\ORM\Utils\NameUtils;
use PDO;

class MySqlSchemaProvider implements SchemaProviderInterface
{
    public function __construct(private readonly DatabaseInterface $database, private readonly TypeConverterInterface $typeConverter)
    {
    }

    public function getDatabaseSchema(): DatabaseSchema
    {
        return new DatabaseSchema(
            $this->getTablesSchema(),
        );
    }

    /** @return array<string, TableSchema> */
    private function getTablesSchema(): array
    {
        $tablesSchema = [];

        $tablesQuery = $this->database->getPdo()->query('SHOW FULL TABLES');
        if ($tablesQuery === false) {
            throw new \RuntimeException('Cannot get tables from database');
        }

        /** @var array<int, array{0: string}> $tables */
        $tables = $tablesQuery->fetchAll();

        foreach ($tables as $table) {
            $tableName = $table[0];

            $tablesSchema[$table[0]] = new TableSchema(
                $tableName,
                $this->getColumnsSchema($tableName),
                $this->getIndexesSchema($tableName),
                $this->getForeignKeysSchema($tableName),
            );
        }

        return $tablesSchema;
    }

    /** @return array<string, ColumnSchema> */
    private function getColumnsSchema(string $tableName): array
    {
        $query = $this->database->getPdo()->prepare('SHOW FULL COLUMNS FROM ' . NameUtils::escape($tableName));
        $query->execute();
        if ($query === false) {
            throw new \RuntimeException('Cannot get columns from table ' . $tableName);
        }

        /**
         * @var array<int, array{
         *     Field: string,
         *     Type: string,
         *     Null: string,
         *     Extra: string,
         *     Key: string,
         *     Default: string|null,
         * }> $columns
         */
        $columns = $query->fetchAll(PDO::FETCH_ASSOC);

        $columnsSchema = [];

        foreach ($columns as $column) {
            $columnType = ColumnType::parseColumnType($column['Type']);

            $columnsSchema[$column['Field']] = new ColumnSchema(
                name: $column['Field'],
                type: $this->typeConverter->convert($columnType->type),
                nullable: $column['Null'] === 'YES',
                autoincrement: $column['Extra'] === 'auto_increment',
                primary: $column['Key'] === 'PRI',
                size: $columnType->size,
                precision: $columnType->precision,
                scale: $columnType->scale,
                enum: $columnType->enum,
                default: $column['Default'],
            );
        }

        return $columnsSchema;
    }

    /** @return array<string, IndexSchema> */
    private function getIndexesSchema(string $tableName): array
    {
        $query = $this->database->getPdo()->prepare('SHOW INDEXES FROM ' . NameUtils::escape($tableName));
        $query->execute();
        if ($query === false) {
            throw new \RuntimeException('Cannot get indexes from table ' . $tableName);
        }

        /**
         * @var array<int, array{
         *     Column_name: string,
         *     Key_name: string,
         *     Non_unique: string,
         * }> $indexes
         */
        $indexes = $query->fetchAll(PDO::FETCH_ASSOC);

        $indexesSchema = [];

        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'PRIMARY') {
                continue;
            }

            $indexesSchema[$index['Key_name']] = new IndexSchema(
                columns: [$index['Column_name']],
                name: $index['Key_name'],
                unique: $index['Non_unique'] === '0',
            );
        }

        return $indexesSchema;
    }

    /** @return array<string, ForeignKeySchema> */
    private function getForeignKeysSchema(string $tableName): array
    {
        $query = $this->database->getPdo()->prepare('SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = :table_name');
        $query->execute([':table_name' => $tableName]);
        if ($query === false) {
            throw new \RuntimeException('Cannot get foreign keys from table ' . $tableName);
        }

        /**
         * @var array<int, array{
         *     COLUMN_NAME: string,
         *     CONSTRAINT_NAME: string,
         *     REFERENCED_TABLE_NAME: string,
         *     REFERENCED_COLUMN_NAME: string,
         * }> $foreignKeys
         */
        $foreignKeys = $query->fetchAll(PDO::FETCH_ASSOC);

        $foreignKeysSchema = [];

        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey['CONSTRAINT_NAME'] === 'PRIMARY') {
                continue;
            }

            $foreignKeysSchema[$foreignKey['CONSTRAINT_NAME']] = new ForeignKeySchema(
                column: $foreignKey['COLUMN_NAME'],
                referenceTable: $foreignKey['REFERENCED_TABLE_NAME'],
                referenceColumn: $foreignKey['REFERENCED_COLUMN_NAME'],
                name: $foreignKey['CONSTRAINT_NAME'],
            );
        }

        return $foreignKeysSchema;
    }
}
