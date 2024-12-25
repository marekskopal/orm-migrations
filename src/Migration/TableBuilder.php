<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

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
use PDO;

class TableBuilder
{
    /** @var list<QueryInterface> */
    private array $queries = [];

    public function __construct(private readonly PDO $pdo, private readonly string $name)
    {
    }

    /** @param list<string>|null $enum */
    public function addColumn(
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
    ): self
    {
        $this->queries[] = new AddColumn($name, $type, $nullable, $autoincrement, $primary, $size, $precision, $scale, $enum, $default);

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
        $this->queries[] = new AddIndex($this->name, $columns, $name, $unique);

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
            array_values(array_filter($this->queries, fn ($query) => $query instanceof AddForeignKey)),
        );
        $this->executeQuery($createTableQuery);

        foreach ($this->queries as $query) {
            if ($query instanceof AddIndex) {
                $this->executeQuery($query);
            }
        }
    }

    public function drop(): void
    {
        $query = new DropTable($this->name);
        $this->executeQuery($query);
    }

    private function executeQuery(QueryInterface $query): void
    {
        $this->pdo->exec($query->getQuery());
    }
}
