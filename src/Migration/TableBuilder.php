<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderInterface;
use MarekSkopal\ORM\Migrations\Migration\Query\AddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\AddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\AddIndex;
use MarekSkopal\ORM\Migrations\Migration\Query\CreateTable;
use MarekSkopal\ORM\Migrations\Migration\Query\DropColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\DropForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\DropIndex;
use MarekSkopal\ORM\Migrations\Migration\Query\DropTable;
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
        string|int|float|null $default = null,
    ): self
    {
        if (is_string($type)) {
            $type = Type::from($type);
        }

        $this->queries[] = new AddColumn(
            name: $name,
            type: $this->databaseProvider->getTypeConverter()->convertToDatabase($type),
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
        $this->queries[] = new DropColumn($name);

        return $this;
    }

    /** @param list<string> $columns */
    public function addIndex(array $columns, string $name, bool $unique): self
    {
        $this->queries[] = new AddIndex($columns, $name, $unique);

        return $this;
    }

    public function dropIndex(string $name): self
    {
        $this->queries[] = new DropIndex($name);

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
        $this->queries[] = new AddForeignKey($column, $referenceTable, $referenceColumn, $name, $onDelete, $onUpdate);

        return $this;
    }

    public function dropForeignKey(string $column): self
    {
        $this->queries[] = new DropForeignKey($column);

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
        $createTableQuery = new CreateTable(
            $this->name,
            array_values(array_filter($this->queries, fn ($query) => $query instanceof AddColumn)),
            array_values(array_filter($this->queries, fn ($query) => $query instanceof AddIndex)),
            array_values(array_filter($this->queries, fn ($query) => $query instanceof AddForeignKey)),
        );
        $this->executeQuery($createTableQuery);
    }

    public function drop(): void
    {
        $query = new DropTable($this->name);
        $this->executeQuery($query);
    }

    private function executeQuery(QueryInterface $query): int
    {
        $pdo = $this->databaseProvider->getDatabase()->getPdo();

        $affectedRows = $pdo->exec($query->getQuery());
        if ($affectedRows === false) {
            $error = $pdo->errorInfo();

            throw new \RuntimeException('Query failed: [' . $error[0] . ']: ' . $error[1] . ' - ' . $error[2]);
        }

        return $affectedRows;
    }
}
