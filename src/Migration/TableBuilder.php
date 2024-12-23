<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use MarekSkopal\ORM\Migrations\Migration\Query\AddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\CreateTable;
use MarekSkopal\ORM\Migrations\Migration\Query\DropColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\DropTable;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use PDO;

class TableBuilder
{
    /** @var list<QueryInterface> */
    private array $queries = [];

    public function __construct(private readonly PDO $pdo, private readonly string $name)
    {
    }

    public function addColumn(
        string $name,
        string $type,
        bool $nullable = false,
        bool $autoincrement = false,
        bool $primary = false,
        string|int|float|null $default = null,
    ): self
    {
        $this->queries[] = new AddColumn($name, $type, $nullable, $autoincrement, $primary, $default);

        return $this;
    }

    public function dropColumn(string $name): self
    {
        $this->queries[] = new DropColumn($name);

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
        $query = new CreateTable($this->name, array_values(array_filter($this->queries, fn ($query) => $query instanceof AddColumn)));
        $this->executeQuery($query);
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
