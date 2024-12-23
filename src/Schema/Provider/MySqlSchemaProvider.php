<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Provider;

use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Utils\ColumnType;
use MarekSkopal\ORM\Utils\NameUtils;
use PDO;

class MySqlSchemaProvider
{
    public function getDatabaseSchema(MySqlDatabase $database): DatabaseSchema
    {
        return new DatabaseSchema(
            $this->getTablesSchema($database),
        );
    }

    /** @return array<string, TableSchema> */
    private function getTablesSchema(MySqlDatabase $database): array
    {
        $tablesSchema = [];

        $tablesQuery = $database->getPdo()->query('SHOW FULL TABLES');
        if ($tablesQuery === false) {
            throw new \RuntimeException('Cannot get tables from database');
        }

        /** @var array<int, array{0: string}> $tables */
        $tables = $tablesQuery->fetchAll();

        foreach ($tables as $table) {
            $tableName = $table[0];

            $query = $database->getPdo()->prepare('SHOW FULL COLUMNS FROM ' . NameUtils::escape($tableName));
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
                    type: $columnType->type,
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

            $tablesSchema[$table[0]] = new TableSchema($tableName, $columnsSchema);
        }

        return $tablesSchema;
    }
}
