<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class MySqlCreateTable implements QueryInterface
{
    /**
     * @param list<MySqlAddColumn> $columns
     * @param list<MySqlAddIndex> $indexes
     * @param list<MySqlAddForeignKey> $foreignKeys
     */
    public function __construct(
        public string $name,
        public array $columns,
        public array $indexes = [],
        public array $foreignKeys = [],
    ) {
    }

    public function getQuery(): string
    {
        return sprintf(
            'CREATE TABLE %s (%s);',
            EscapeUtils::escape($this->name),
            implode(', ', $this->getQueries()),
        );
    }

    /** @return list<string> */
    private function getQueries(): array
    {
        return array_values(array_filter([
            $this->getColumnsQuery(),
            $this->getIndexesQuery(),
            $this->getForeignKeysQuery(),
        ], fn(string $query): bool => $query !== ''));
    }

    private function getColumnsQuery(): string
    {
        return implode(', ', array_map(fn(MySqlAddColumn $col): string => $col->getQuery(), $this->columns));
    }

    private function getIndexesQuery(): string
    {
        return implode(', ', array_map(fn(MySqlAddIndex $idx): string => $idx->getQuery(), $this->indexes));
    }

    private function getForeignKeysQuery(): string
    {
        return implode(', ', array_map(fn(MySqlAddForeignKey $fk): string => $fk->getQuery(), $this->foreignKeys));
    }
}
