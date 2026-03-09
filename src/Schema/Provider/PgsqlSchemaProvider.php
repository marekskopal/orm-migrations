<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\MigrationRepository;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\TypeConverterInterface;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use PDO;

class PgsqlSchemaProvider implements SchemaProviderInterface
{
    public function __construct(private readonly DatabaseInterface $database, private readonly TypeConverterInterface $typeConverter,)
    {
    }

    public function getDatabaseSchema(): DatabaseSchema
    {
        return new DatabaseSchema($this->getTablesSchema());
    }

    /** @return array<string, TableSchema> */
    private function getTablesSchema(): array
    {
        $query = $this->database->getPdo()->prepare(
            'SELECT table_name FROM information_schema.tables WHERE table_schema = :schema AND table_type = :type',
        );
        $query->execute([':schema' => 'public', ':type' => 'BASE TABLE']);

        /** @var list<string> $tableNames */
        $tableNames = $query->fetchAll(PDO::FETCH_COLUMN);

        $tablesSchema = [];

        foreach ($tableNames as $tableName) {
            if ($tableName === MigrationRepository::MigrationTable) {
                continue;
            }

            $primaryKeys = $this->getPrimaryKeyColumns($tableName);

            $tablesSchema[$tableName] = new TableSchema(
                $tableName,
                $this->getColumnsSchema($tableName, $primaryKeys),
                $this->getIndexesSchema($tableName),
                $this->getForeignKeysSchema($tableName),
            );
        }

        return $tablesSchema;
    }

    /** @return list<string> */
    private function getPrimaryKeyColumns(string $tableName): array
    {
        $query = $this->database->getPdo()->prepare(
            'SELECT kcu.column_name' .
            ' FROM information_schema.table_constraints tc' .
            ' JOIN information_schema.key_column_usage kcu' .
            '   ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema' .
            ' WHERE tc.table_schema = :schema AND tc.table_name = :table AND tc.constraint_type = :type',
        );
        $query->execute([':schema' => 'public', ':table' => $tableName, ':type' => 'PRIMARY KEY']);

        /** @var list<string> $columns */
        $columns = $query->fetchAll(PDO::FETCH_COLUMN);

        return $columns;
    }

    /**
     * @param list<string> $primaryKeys
     * @return array<string, ColumnSchema>
     */
    private function getColumnsSchema(string $tableName, array $primaryKeys): array
    {
        $query = $this->database->getPdo()->prepare(
            'SELECT column_name, data_type, is_nullable, column_default,' .
            ' character_maximum_length, numeric_precision, numeric_scale, is_identity' .
            ' FROM information_schema.columns' .
            ' WHERE table_schema = :schema AND table_name = :table' .
            ' ORDER BY ordinal_position',
        );
        $query->execute([':schema' => 'public', ':table' => $tableName]);

        /**
         * @var array<int, array{
         *     column_name: string,
         *     data_type: string,
         *     is_nullable: string,
         *     column_default: string|null,
         *     character_maximum_length: string|null,
         *     numeric_precision: string|null,
         *     numeric_scale: string|null,
         *     is_identity: string,
         * }> $columns
         */
        $columns = $query->fetchAll(PDO::FETCH_ASSOC);

        $enumConstraints = $this->getEnumConstraints($tableName);

        $columnsSchema = [];

        foreach ($columns as $column) {
            $enumValues = $enumConstraints[$column['column_name']] ?? null;
            $type = $enumValues !== null
                ? Type::Enum
                : $this->typeConverter->convert($column['data_type']);
            $autoincrement = $column['is_identity'] === 'YES'
                || str_starts_with($column['column_default'] ?? '', 'nextval(');

            $columnsSchema[$column['column_name']] = new ColumnSchema(
                name: $column['column_name'],
                type: $type,
                nullable: $column['is_nullable'] === 'YES',
                autoincrement: $autoincrement,
                primary: in_array($column['column_name'], $primaryKeys, true),
                size: $column['character_maximum_length'] !== null ? (int) $column['character_maximum_length'] : null,
                precision: $column['numeric_precision'] !== null ? (int) $column['numeric_precision'] : null,
                scale: $column['numeric_scale'] !== null ? (int) $column['numeric_scale'] : null,
                enum: $enumValues,
                default: $autoincrement ? null : $column['column_default'],
            );
        }

        return $columnsSchema;
    }

    /** @return array<string, list<string>> */
    private function getEnumConstraints(string $tableName): array
    {
        $query = $this->database->getPdo()->prepare(
            'SELECT a.attname AS column_name, pg_get_constraintdef(con.oid) AS constraint_def' .
            ' FROM pg_constraint con' .
            ' JOIN pg_class rel ON rel.oid = con.conrelid' .
            ' JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace' .
            ' JOIN LATERAL unnest(con.conkey) AS conkey_elem ON true' .
            ' JOIN pg_attribute a ON a.attrelid = rel.oid AND a.attnum = conkey_elem' .
            ' WHERE con.contype = :contype AND rel.relname = :table AND nsp.nspname = :schema',
        );
        $query->execute([':contype' => 'c', ':table' => $tableName, ':schema' => 'public']);

        /** @var array<int, array{column_name: string, constraint_def: string}> $rows */
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        $enumConstraints = [];

        foreach ($rows as $row) {
            // PostgreSQL normalises IN (...) to = ANY (ARRAY[...]) internally.
            // pg_get_constraintdef() returns e.g.:
            //   CHECK ((status = ANY (ARRAY['active'::text, 'inactive'::text])))
            if (preg_match('/= ANY \(ARRAY\[(.+?)]\)/i', $row['constraint_def'], $arrayMatch) !== 1) {
                continue;
            }

            preg_match_all("/'([^']+)'/", $arrayMatch[1], $valueMatches);
            if (count($valueMatches[1]) === 0) {
                continue;
            }

            $enumConstraints[$row['column_name']] = $valueMatches[1];
        }

        return $enumConstraints;
    }

    /** @return array<string, IndexSchema> */
    private function getIndexesSchema(string $tableName): array
    {
        $query = $this->database->getPdo()->prepare(
            'SELECT i.relname AS index_name, a.attname AS column_name, ix.indisunique AS is_unique' .
            ' FROM pg_class t' .
            ' JOIN pg_index ix ON t.oid = ix.indrelid' .
            ' JOIN pg_class i ON i.oid = ix.indexrelid' .
            ' JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(ix.indkey)' .
            ' JOIN pg_namespace n ON t.relnamespace = n.oid' .
            ' WHERE t.relkind = :relkind AND n.nspname = :schema AND t.relname = :table AND NOT ix.indisprimary',
        );
        $query->execute([':relkind' => 'r', ':schema' => 'public', ':table' => $tableName]);

        /**
         * @var array<int, array{
         *     index_name: string,
         *     column_name: string,
         *     is_unique: bool|string,
         * }> $indexes
         */
        $indexes = $query->fetchAll(PDO::FETCH_ASSOC);

        $indexesSchema = [];

        foreach ($indexes as $index) {
            if (isset($indexesSchema[$index['index_name']])) {
                $existing = $indexesSchema[$index['index_name']];
                $indexesSchema[$index['index_name']] = new IndexSchema(
                    columns: [...$existing->columns, $index['column_name']],
                    name: $existing->name,
                    unique: $existing->unique,
                );
            } else {
                $indexesSchema[$index['index_name']] = new IndexSchema(
                    columns: [$index['column_name']],
                    name: $index['index_name'],
                    unique: $index['is_unique'] === true || $index['is_unique'] === 't',
                );
            }
        }

        return $indexesSchema;
    }

    /** @return array<string, ForeignKeySchema> */
    private function getForeignKeysSchema(string $tableName): array
    {
        $query = $this->database->getPdo()->prepare(
            'SELECT kcu.column_name, kcu.constraint_name, ccu.table_name AS referenced_table_name, ccu.column_name AS referenced_column_name' .
            ' FROM information_schema.key_column_usage kcu' .
            ' JOIN information_schema.referential_constraints rc' .
            '   ON kcu.constraint_name = rc.constraint_name AND kcu.constraint_schema = rc.constraint_schema' .
            ' JOIN information_schema.constraint_column_usage ccu' .
            '   ON rc.unique_constraint_name = ccu.constraint_name AND rc.unique_constraint_schema = ccu.constraint_schema' .
            ' WHERE kcu.table_schema = :schema AND kcu.table_name = :table',
        );
        $query->execute([':schema' => 'public', ':table' => $tableName]);

        /**
         * @var array<int, array{
         *     column_name: string,
         *     constraint_name: string,
         *     referenced_table_name: string,
         *     referenced_column_name: string,
         * }> $foreignKeys
         */
        $foreignKeys = $query->fetchAll(PDO::FETCH_ASSOC);

        $foreignKeysSchema = [];

        foreach ($foreignKeys as $foreignKey) {
            $foreignKeysSchema[$foreignKey['constraint_name']] = new ForeignKeySchema(
                column: $foreignKey['column_name'],
                referenceTable: $foreignKey['referenced_table_name'],
                referenceColumn: $foreignKey['referenced_column_name'],
                name: $foreignKey['constraint_name'],
            );
        }

        return $foreignKeysSchema;
    }
}
