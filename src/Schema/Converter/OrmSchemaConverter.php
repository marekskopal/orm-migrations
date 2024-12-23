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
                    name: $column->columnName,
                    type: $column->columnType,
                    nullable: $column->isNullable,
                    autoincrement: $column->isAutoIncrement,
                    primary: $column->isPrimary,
                    size: $column->size,
                    precision: $column->precision,
                    scale: $column->scale,
                    enum: $column->enumClass !== null ? array_map(
                        fn($case) => (string) $case,
                        array_column($column->enumClass::cases(), 'value'),
                    ) : null,
                    default: $column->default,
                );
            }

            $tables[$entity->table] = new TableSchema($entity->table, $columns);
        }

        return new DatabaseSchema($tables);
    }
}
