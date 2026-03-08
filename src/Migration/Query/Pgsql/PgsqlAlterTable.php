<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class PgsqlAlterTable implements QueryInterface
{
    /**
     * @param list<PgsqlAddColumn> $columnToCreate
     * @param list<PgsqlDropColumn> $columnToDrop
     * @param list<PgsqlAlterColumn> $columnToAlter
     * @param list<PgsqlAddForeignKey> $foreignKeysToCreate
     * @param list<PgsqlDropForeignKey> $foreignKeysToDrop
     */
    public function __construct(
        public string $name,
        public array $columnToCreate = [],
        public array $columnToDrop = [],
        public array $columnToAlter = [],
        public array $foreignKeysToCreate = [],
        public array $foreignKeysToDrop = [],
    ) {
    }

    public function getQuery(): string
    {
        return sprintf(
            'ALTER TABLE %s %s;',
            EscapeUtils::escape($this->name, '"'),
            implode(', ', $this->getQueries()),
        );
    }

    public function isEmpty(): bool
    {
        return $this->columnToCreate === []
            && $this->columnToDrop === []
            && $this->columnToAlter === []
            && $this->foreignKeysToCreate === []
            && $this->foreignKeysToDrop === [];
    }

    /** @return list<string> */
    private function getQueries(): array
    {
        return array_values(array_filter([
            $this->getAddColumnsQuery(),
            $this->getDropColumnsQuery(),
            $this->getAlterColumnsQuery(),
            $this->getAddForeignKeysQuery(),
            $this->getDropForeignKeysQuery(),
        ], fn(string $query): bool => $query !== ''));
    }

    private function getAddColumnsQuery(): string
    {
        return implode(', ', array_map(
            fn(PgsqlAddColumn $col): string => 'ADD ' . $col->getQuery(),
            $this->columnToCreate,
        ));
    }

    private function getDropColumnsQuery(): string
    {
        return implode(', ', array_map(
            fn(PgsqlDropColumn $col): string => $col->getQuery(),
            $this->columnToDrop,
        ));
    }

    private function getAlterColumnsQuery(): string
    {
        $clauses = [];
        foreach ($this->columnToAlter as $column) {
            $clauses = array_merge($clauses, $column->getAlterClauses());
        }

        return implode(', ', $clauses);
    }

    private function getAddForeignKeysQuery(): string
    {
        return implode(', ', array_map(
            fn(PgsqlAddForeignKey $fk): string => 'ADD ' . $fk->getQuery(),
            $this->foreignKeysToCreate,
        ));
    }

    private function getDropForeignKeysQuery(): string
    {
        return implode(', ', array_map(
            fn(PgsqlDropForeignKey $fk): string => $fk->getQuery(),
            $this->foreignKeysToDrop,
        ));
    }
}
