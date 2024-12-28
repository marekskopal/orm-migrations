<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Utils\NameUtils;

readonly class AlterTable implements QueryInterface
{
    /**
     * @param string $name
     * @param list<AddColumn> $columnToCreate
     * @param list<DropColumn> $columnToDrop
     * @param list<AlterColumn> $columnToAlter
     * @param list<AddIndex> $indexesToCreate
     * @param list<DropIndex> $indexesToDrop
     * @param list<AddForeignKey> $foreignKeysToCreate
     * @param list<DropForeignKey> $foreignKeysToDrop
     */
    public function __construct(
        public string $name,
        public array $columnToCreate = [],
        public array $columnToDrop = [],
        public array $columnToAlter = [],
        public array $indexesToCreate = [],
        public array $indexesToDrop = [],
        public array $foreignKeysToCreate = [],
        public array $foreignKeysToDrop = [],
    ) {
    }

    public function getQuery(): string
    {
        return sprintf(
            'ALTER TABLE %s %s;',
            NameUtils::escape($this->name),
            implode(', ', $this->getQueries()),
        );
    }

    /** @return list<string> */
    private function getQueries(): array
    {
        $queries = array_values(array_filter([
            $this->getAddColumnsQuery(),
            $this->getDropColumnsQuery(),
            $this->getAlterColumnsQuery(),
            $this->getAddIndexesQuery(),
            $this->getDropIndexesQuery(),
            $this->getAddForeignKeysQuery(),
            $this->getDropForeignKeysQuery(),
        ], fn (string $query): bool => $query !== ''));

        return $queries;
    }

    private function getAddColumnsQuery(): string
    {
        $columns = [];

        foreach ($this->columnToCreate as $column) {
            $columns[] = 'ADD ' . $column->getQuery();
        }

        return implode(', ', $columns);
    }

    private function getDropColumnsQuery(): string
    {
        $columns = [];

        foreach ($this->columnToDrop as $column) {
            $columns[] = $column->getQuery();
        }

        return implode(', ', $columns);
    }

    private function getAlterColumnsQuery(): string
    {
        $columns = [];

        foreach ($this->columnToAlter as $column) {
            $columns[] = sprintf('CHANGE %s %s', NameUtils::escape($column->name), $column->getQuery());
        }

        return implode(', ', $columns);
    }

    private function getAddIndexesQuery(): string
    {
        $indexes = [];

        foreach ($this->indexesToCreate as $index) {
            $indexes[] = 'ADD ' . $index->getQuery();
        }

        return implode(', ', $indexes);
    }

    private function getDropIndexesQuery(): string
    {
        $indexes = [];

        foreach ($this->indexesToDrop as $index) {
            $indexes[] = $index->getQuery();
        }

        return implode(', ', $indexes);
    }

    private function getAddForeignKeysQuery(): string
    {
        $foreignKeys = [];

        foreach ($this->foreignKeysToCreate as $foreignKey) {
            $foreignKeys[] = 'ADD ' . $foreignKey->getQuery();
        }

        return implode(', ', $foreignKeys);
    }

    private function getDropForeignKeysQuery(): string
    {
        $foreignKeys = [];

        foreach ($this->foreignKeysToDrop as $foreignKey) {
            $foreignKeys[] = $foreignKey->getQuery();
        }

        return implode(', ', $foreignKeys);
    }
}
