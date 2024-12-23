<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Converter;

use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Schema\Schema;

class OrmSchemaConverter
{
    public function convert(Schema $schema): DatabaseSchema
    {
        $tables = [];

        foreach ($schema->entities as $entity) {
            $columns = [];

            foreach ($entity->columns as $column) {
                $columns[$column->columnName] = new ColumnSchema(
                    $column->columnName,
                    $column->columnType,
                    $column->isNullable,
                    $column->isAutoIncrement,
                    $column->isNullable,
                    $column->default,
                );
            }

            $tables[$entity->table] = new TableSchema($entity->table, $columns);
        }

        return new DatabaseSchema($tables);
    }
}
