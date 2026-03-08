<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Pgsql;

use BackedEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryFactoryInterface;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\TypeConverterInterface;

final class PgsqlQueryFactory implements QueryFactoryInterface
{
    public function __construct(private readonly TypeConverterInterface $typeConverter)
    {
    }

    /** @param list<string>|null $enum */
    public function createAddColumn(
        string $name,
        Type $type,
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
        return new PgsqlAddColumn(
            $name,
            $this->typeConverter->convertToDatabase($type),
            $nullable,
            $autoincrement,
            $primary,
            $this->typeConverter->sanitizeSize($type, $size),
            $precision,
            $scale,
            $enum,
            $default,
        );
    }

    /** @param list<string>|null $enum */
    public function createAlterColumn(
        string $name,
        Type $type,
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
        return new PgsqlAlterColumn(
            $name,
            $this->typeConverter->convertToDatabase($type),
            $nullable,
            $autoincrement,
            $primary,
            $this->typeConverter->sanitizeSize($type, $size),
            $precision,
            $scale,
            $enum,
            $default,
        );
    }

    public function createDropColumn(string $name): QueryInterface
    {
        return new PgsqlDropColumn($name);
    }

    /** @param list<string> $columns */
    public function createAddIndex(array $columns, string $name, bool $unique, string $tableName): QueryInterface
    {
        return new PgsqlCreateIndex($columns, $name, $unique, $tableName);
    }

    public function createDropIndex(string $name): QueryInterface
    {
        return new PgsqlDropIndex($name);
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
        return new PgsqlAddForeignKey($column, $referenceTable, $referenceColumn, $name, $onDelete, $onUpdate);
    }

    public function createDropForeignKey(string $name): QueryInterface
    {
        return new PgsqlDropForeignKey($name);
    }

    public function createDropTable(string $name): QueryInterface
    {
        return new PgsqlDropTable($name);
    }

    /** @param array<array<string|int|float|bool|BackedEnum|null>> $values */
    public function createInsert(string $name, array $values): QueryInterface
    {
        return new PgsqlInsert($name, $values);
    }

    /**
     * @param list<QueryInterface> $queries
     * @return list<QueryInterface>
     */
    public function buildCreate(string $tableName, array $queries): array
    {
        $result = [];

        $result[] = new PgsqlCreateTable(
            $tableName,
            array_values(array_filter($queries, fn($q) => $q instanceof PgsqlAddColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof PgsqlAddForeignKey)),
        );

        foreach (array_filter($queries, fn($q) => $q instanceof PgsqlCreateIndex) as $index) {
            $result[] = $index;
        }

        return $result;
    }

    /**
     * @param list<QueryInterface> $queries
     * @return list<QueryInterface>
     */
    public function buildAlter(string $tableName, array $queries): array
    {
        $alterTable = new PgsqlAlterTable(
            $tableName,
            array_values(array_filter($queries, fn($q) => $q instanceof PgsqlAddColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof PgsqlDropColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof PgsqlAlterColumn)),
            array_values(array_filter($queries, fn($q) => $q instanceof PgsqlAddForeignKey)),
            array_values(array_filter($queries, fn($q) => $q instanceof PgsqlDropForeignKey)),
        );

        $result = [];

        if (!$alterTable->isEmpty()) {
            $result[] = $alterTable;
        }

        foreach (array_filter($queries, fn($q) => $q instanceof PgsqlCreateIndex) as $index) {
            $result[] = $index;
        }

        foreach (array_filter($queries, fn($q) => $q instanceof PgsqlDropIndex) as $index) {
            $result[] = $index;
        }

        return $result;
    }
}
