<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Utils\NameUtils;

readonly class CreateTable implements QueryInterface
{
    /**
     * @param string $name
     * @param list<AddColumn> $columns
     * @param list<AddIndex> $indexes
     * @param list<AddForeignKey> $foreignKeys
     */
    public function __construct(public string $name, public array $columns, public array $indexes = [], public array $foreignKeys = [])
    {
    }

    public function getQuery(): string
    {
        return sprintf(
            'CREATE TABLE %s (%s);',
            NameUtils::escape($this->name),
            implode(', ', $this->getQueries()),
        );
    }

    /** @return list<string> */
    private function getQueries(): array
    {
        $queries = array_values(array_filter([
            $this->getColumnsQuery(),
            $this->getIndexesQuery(),
            $this->getForeignKeysQuery(),
        ], fn (string $query): bool => $query !== ''));

        return $queries;
    }

    private function getColumnsQuery(): string
    {
        $columns = [];

        foreach ($this->columns as $column) {
            $columns[] = $column->getQuery();
        }

        return implode(', ', $columns);
    }

    private function getIndexesQuery(): string
    {
        $indexes = [];

        foreach ($this->indexes as $index) {
            $indexes[] = $index->getQuery();
        }

        return implode(', ', $indexes);
    }

    private function getForeignKeysQuery(): string
    {
        $foreignKeys = [];

        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKeys[] = $foreignKey->getQuery();
        }

        return implode(', ', $foreignKeys);
    }
}
