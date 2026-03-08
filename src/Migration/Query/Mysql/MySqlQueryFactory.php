<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Mysql;

use BackedEnum;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryFactoryInterface;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;

final class MySqlQueryFactory implements QueryFactoryInterface
{
    /** @param list<string>|null $enum */
    public function createAddColumn(
        string $name,
        string $type,
        bool $nullable = false,
        bool $autoincrement = false,
        bool $primary = false,
        ?int $size = null,
        ?int $precision = null,
        ?int $scale = null,
        ?array $enum = null,
        string|int|float|bool|null $default = null,
    ): QueryInterface
    {
        return new MySqlAddColumn($name, $type, $nullable, $autoincrement, $primary, $size, $precision, $scale, $enum, $default);
    }

    /** @param list<string>|null $enum */
    public function createAlterColumn(
        string $name,
        string $type,
        bool $nullable = false,
        bool $autoincrement = false,
        bool $primary = false,
        ?int $size = null,
        ?int $precision = null,
        ?int $scale = null,
        ?array $enum = null,
        string|int|float|null $default = null,
    ): QueryInterface
    {
        return new MySqlAlterColumn($name, $type, $nullable, $autoincrement, $primary, $size, $precision, $scale, $enum, $default);
    }

    public function createDropColumn(string $name): QueryInterface
    {
        return new MySqlDropColumn($name);
    }

    /** @param list<string> $columns */
    public function createAddIndex(array $columns, string $name, bool $unique, string $tableName): QueryInterface
    {
        return new MySqlAddIndex($columns, $name, $unique);
    }

    public function createDropIndex(string $name): QueryInterface
    {
        return new MySqlDropIndex($name);
    }

    public function createAddForeignKey(
        string $column,
        string $referenceTable,
        string $referenceColumn,
        ?string $name = null,
        ReferenceOptionEnum $onDelete = ReferenceOptionEnum::Cascade,
        ReferenceOptionEnum $onUpdate = ReferenceOptionEnum::Cascade,
    ): QueryInterface
    {
        return new MySqlAddForeignKey($column, $referenceTable, $referenceColumn, $name, $onDelete, $onUpdate);
    }

    public function createDropForeignKey(string $name): QueryInterface
    {
        return new MySqlDropForeignKey($name);
    }

    public function createDropTable(string $name): QueryInterface
    {
        return new MySqlDropTable($name);
    }

    /** @param array<array<string|int|float|bool|BackedEnum|null>> $values */
    public function createInsert(string $name, array $values): QueryInterface
    {
        return new MySqlInsert($name, $values);
    }

    /**
     * @param list<QueryInterface> $queries
     * @return list<QueryInterface>
     */
    public function buildCreate(string $tableName, array $queries): array
    {
        return [new MySqlCreateTable(
            $tableName,
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlAddColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlAddIndex)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlAddForeignKey)),
        )];
    }

    /**
     * @param list<QueryInterface> $queries
     * @return list<QueryInterface>
     */
    public function buildAlter(string $tableName, array $queries): array
    {
        return [new MySqlAlterTable(
            $tableName,
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlAddColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlDropColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlAlterColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlAddIndex)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlDropIndex)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlAddForeignKey)),
            array_values(array_filter($queries, fn($q) => $q instanceof MySqlDropForeignKey)),
        )];
    }
}
