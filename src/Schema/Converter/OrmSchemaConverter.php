<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Converter;

use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Utils\EnumUtils;
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
                    enum: $column->enumClass !== null ? EnumUtils::getEnumValues($column->enumClass) : null,
                    default: $column->default,
                );
            }

            $indexes = [];

            $foreignKeys = [];

            foreach ($entity->columns as $column) {
                if ($column->relationType === null) {
                    continue;
                }

                $relationEntitySchema = $schema->entities[$column->relationEntityClass];
                $relationColumn = $column->relationColumnName;
                if ($relationColumn === null) {
                    $relationColumn = $relationEntitySchema->getPrimaryColumn()->columnName;
                }

                $indexes[$column->columnName] = new IndexSchema(
                    columns: [$column->columnName],
                    name: implode('_', [$entity->table, $column->columnName, 'index']),
                    unique: false,
                );

                $foreignKeys[$column->columnName] = new ForeignKeySchema(
                    column: $column->columnName,
                    referenceTable: $relationEntitySchema->table,
                    referenceColumn: $relationColumn,
                    name: implode(
                        '_',
                        [$entity->table, $column->columnName, $relationEntitySchema->table, $relationColumn, 'fk'],
                    ),
                );
            }

            $tables[$entity->table] = new TableSchema($entity->table, $columns, $indexes, $foreignKeys);
        }

        return new DatabaseSchema($tables);
    }
}
