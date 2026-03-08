<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Converter;

use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Utils\EnumUtils;
use MarekSkopal\ORM\Schema\Enum\RelationEnum;
use MarekSkopal\ORM\Schema\Schema;

class OrmSchemaConverter
{
    public function convert(Schema $schema): DatabaseSchema
    {
        $tables = [];

        foreach ($schema->entities as $entity) {
            $columns = [];

            foreach ($entity->columns as $column) {
                if (in_array($column->relationType, [
                    RelationEnum::OneToMany,
                    RelationEnum::OneToOneInverse,
                    RelationEnum::ManyToMany,
                    RelationEnum::ManyToManyInverse,
                ], true)) {
                    continue;
                }

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
                if ($column->relationType === null || in_array($column->relationType, [
                    RelationEnum::OneToMany,
                    RelationEnum::OneToOneInverse,
                    RelationEnum::ManyToMany,
                    RelationEnum::ManyToManyInverse,
                ], true)) {
                    continue;
                }

                /** @phpstan-ignore-next-line offsetAccess.notFound */
                $relationEntitySchema = $schema->entities[$column->relationEntityClass];
                $relationColumn = $column->relationColumnName;
                if ($relationColumn === null) {
                    $relationColumn = $relationEntitySchema->getPrimaryColumn()->columnName;
                }

                $indexes[$column->columnName] = new IndexSchema(
                    columns: [$column->columnName],
                    name: implode('_', [$entity->table, $column->columnName, 'index']),
                    unique: $column->relationType === RelationEnum::OneToOne,
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

            foreach ($entity->columns as $column) {
                if ($column->relationType !== RelationEnum::ManyToMany) {
                    continue;
                }

                /** @phpstan-ignore-next-line offsetAccess.notFound */
                $relationEntitySchema = $schema->entities[$column->relationEntityClass];
                $joinTable = $column->joinTable ?? throw new \RuntimeException(
                    sprintf('ManyToMany column "%s" is missing joinTable', $column->columnName),
                );
                $joinColumn = $column->joinColumn ?? throw new \RuntimeException(
                    sprintf('ManyToMany column "%s" is missing joinColumn', $column->columnName),
                );
                $inverseJoinColumn = $column->inverseJoinColumn ?? throw new \RuntimeException(
                    sprintf('ManyToMany column "%s" is missing inverseJoinColumn', $column->columnName),
                );

                $joinColumns = [
                    $joinColumn => new ColumnSchema(
                        name: $joinColumn,
                        type: $entity->getPrimaryColumn()->columnType,
                        nullable: false,
                        autoincrement: false,
                        primary: false,
                        size: null,
                        precision: null,
                        scale: null,
                        enum: null,
                        default: null,
                    ),
                    $inverseJoinColumn => new ColumnSchema(
                        name: $inverseJoinColumn,
                        type: $relationEntitySchema->getPrimaryColumn()->columnType,
                        nullable: false,
                        autoincrement: false,
                        primary: false,
                        size: null,
                        precision: null,
                        scale: null,
                        enum: null,
                        default: null,
                    ),
                ];

                $entityPrimaryColumn = $entity->getPrimaryColumn()->columnName;
                $relationPrimaryColumn = $relationEntitySchema->getPrimaryColumn()->columnName;

                $joinIndexes = [
                    $joinColumn => new IndexSchema(
                        columns: [$joinColumn],
                        name: implode('_', [$joinTable, $joinColumn, 'index']),
                        unique: false,
                    ),
                    $inverseJoinColumn => new IndexSchema(
                        columns: [$inverseJoinColumn],
                        name: implode('_', [$joinTable, $inverseJoinColumn, 'index']),
                        unique: false,
                    ),
                ];

                $joinForeignKeys = [
                    $joinColumn => new ForeignKeySchema(
                        column: $joinColumn,
                        referenceTable: $entity->table,
                        referenceColumn: $entityPrimaryColumn,
                        name: implode('_', [$joinTable, $joinColumn, $entity->table, $entityPrimaryColumn, 'fk']),
                    ),
                    $inverseJoinColumn => new ForeignKeySchema(
                        column: $inverseJoinColumn,
                        referenceTable: $relationEntitySchema->table,
                        referenceColumn: $relationPrimaryColumn,
                        name: implode(
                            '_',
                            [$joinTable, $inverseJoinColumn, $relationEntitySchema->table, $relationPrimaryColumn, 'fk'],
                        ),
                    ),
                ];

                $tables[$joinTable] = new TableSchema($joinTable, $joinColumns, $joinIndexes, $joinForeignKeys);
            }
        }

        return new DatabaseSchema($tables);
    }
}
