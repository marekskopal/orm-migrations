<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use BackedEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;

interface QueryFactoryInterface
{
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
    ): QueryInterface;

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
    ): QueryInterface;

    public function createDropColumn(string $name): QueryInterface;

    /** @param list<string> $columns */
    public function createAddIndex(array $columns, string $name, bool $unique, string $tableName): QueryInterface;

    public function createDropIndex(string $name): QueryInterface;

    public function createAddForeignKey(
        string $column,
        string $referenceTable,
        string $referenceColumn,
        ?string $name = null,
        ReferenceOptionEnum $onDelete = ReferenceOptionEnum::Cascade,
        ReferenceOptionEnum $onUpdate = ReferenceOptionEnum::Cascade,
    ): QueryInterface;

    public function createDropForeignKey(string $name): QueryInterface;

    public function createDropTable(string $name): QueryInterface;

    /** @param array<array<string|int|float|bool|BackedEnum|null>> $values */
    public function createInsert(string $name, array $values): QueryInterface;

    /**
     * @param list<QueryInterface> $queries
     * @return list<QueryInterface>
     */
    public function buildCreate(string $tableName, array $queries): array;

    /**
     * @param list<QueryInterface> $queries
     * @return list<QueryInterface>
     */
    public function buildAlter(string $tableName, array $queries): array;
}
