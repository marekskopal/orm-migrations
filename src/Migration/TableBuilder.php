<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use BackedEnum;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderInterface;
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;

class TableBuilder
{
    /** @var list<QueryInterface> */
    private array $queries = [];

    public function __construct(private readonly DatabaseProviderInterface $databaseProvider, private readonly string $name)
    {
    }

    /** @param list<string>|null $enum */
    public function addColumn(
        string $name,
        Type|string $type,
        bool $nullable = false,
        bool $autoincrement = false,
        bool $primary = false,
        ?int $size = null,
        ?int $precision = null,
        ?int $scale = null,
        ?array $enum = null,
        string|int|float|bool|null $default = null,
    ): self
    {
        if (is_string($type)) {
            $type = Type::from($type);
        }

        $this->queries[] = $this->databaseProvider->getQueryFactory()->createAddColumn(
            name: $name,
            type: $type,
            nullable: $nullable,
            autoincrement: $autoincrement,
            primary: $primary,
            size: $size,
            precision: $precision,
            scale: $scale,
            enum: $enum,
            default: $default,
        );

        return $this;
    }

    public function dropColumn(string $name): self
    {
        $this->queries[] = $this->databaseProvider->getQueryFactory()->createDropColumn($name);

        return $this;
    }

    /** @param list<string>|null $enum */
    public function alterColumn(
        string $name,
        Type|string $type,
        bool $nullable = false,
        bool $autoincrement = false,
        bool $primary = false,
        ?int $size = null,
        ?int $precision = null,
        ?int $scale = null,
        ?array $enum = null,
        string|int|float|null $default = null,
    ): self {
        if (is_string($type)) {
            $type = Type::from($type);
        }

        $this->queries[] = $this->databaseProvider->getQueryFactory()->createAlterColumn(
            name: $name,
            type: $type,
            nullable: $nullable,
            autoincrement: $autoincrement,
            primary: $primary,
            size: $size,
            precision: $precision,
            scale: $scale,
            enum: $enum,
            default: $default,
        );

        return $this;
    }

    /** @param list<string> $columns */
    public function addIndex(array $columns, string $name, bool $unique): self
    {
        $this->queries[] = $this->databaseProvider->getQueryFactory()->createAddIndex($columns, $name, $unique, $this->name);

        return $this;
    }

    public function dropIndex(string $name): self
    {
        $this->queries[] = $this->databaseProvider->getQueryFactory()->createDropIndex($name);

        return $this;
    }

    public function addForeignKey(
        string $column,
        string $referenceTable,
        string $referenceColumn,
        ?string $name = null,
        ReferenceOptionEnum $onDelete = ReferenceOptionEnum::Cascade,
        ReferenceOptionEnum $onUpdate = ReferenceOptionEnum::Cascade,
    ): self {
        $this->queries[] = $this->databaseProvider->getQueryFactory()->createAddForeignKey(
            $column,
            $referenceTable,
            $referenceColumn,
            $name,
            $onDelete,
            $onUpdate,
        );

        return $this;
    }

    public function dropForeignKey(string $column): self
    {
        $this->queries[] = $this->databaseProvider->getQueryFactory()->createDropForeignKey($column);

        return $this;
    }

    public function execute(): void
    {
        foreach ($this->queries as $query) {
            $this->executeQuery($query);
        }
    }

    public function create(): void
    {
        foreach ($this->databaseProvider->getQueryFactory()->buildCreate($this->name, $this->queries) as $query) {
            $this->executeQuery($query);
        }
    }

    public function drop(): void
    {
        $this->executeQuery($this->databaseProvider->getQueryFactory()->createDropTable($this->name));
    }

    public function alter(): void
    {
        foreach ($this->databaseProvider->getQueryFactory()->buildAlter($this->name, $this->queries) as $query) {
            $this->executeQuery($query);
        }
    }

    /** @param array<array<string|int|float|bool|BackedEnum|null>> $values */
    public function insert(array $values): void
    {
        $this->executeQuery($this->databaseProvider->getQueryFactory()->createInsert($this->name, $values));
    }

    private function executeQuery(QueryInterface $query): int
    {
        $pdo = $this->databaseProvider->getDatabase()->getPdo();

        $affectedRows = $pdo->exec($query->getQuery());
        if ($affectedRows === false) {
            /** @var array{0: string, 1: string, 2: string} $error */
            $error = $pdo->errorInfo();

            throw new \RuntimeException('Query failed: [' . $error[0] . ']: ' . $error[1] . ' - ' . $error[2]);
        }

        return $affectedRows;
    }
}
