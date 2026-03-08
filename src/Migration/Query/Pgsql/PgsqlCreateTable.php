<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class PgsqlCreateTable implements QueryInterface
{
    /**
     * @param list<PgsqlAddColumn> $columns
     * @param list<PgsqlAddForeignKey> $foreignKeys
     */
    public function __construct(public string $name, public array $columns, public array $foreignKeys = [],)
    {
    }

    public function getQuery(): string
    {
        return sprintf(
            'CREATE TABLE %s (%s);',
            EscapeUtils::escape($this->name, '"'),
            implode(', ', $this->getQueries()),
        );
    }

    /** @return list<string> */
    private function getQueries(): array
    {
        return array_values(array_filter([
            $this->getColumnsQuery(),
            $this->getForeignKeysQuery(),
        ], fn(string $query): bool => $query !== ''));
    }

    private function getColumnsQuery(): string
    {
        return implode(', ', array_map(fn(PgsqlAddColumn $col): string => $col->getQuery(), $this->columns));
    }

    private function getForeignKeysQuery(): string
    {
        return implode(', ', array_map(fn(PgsqlAddForeignKey $fk): string => $fk->getQuery(), $this->foreignKeys));
    }
}
