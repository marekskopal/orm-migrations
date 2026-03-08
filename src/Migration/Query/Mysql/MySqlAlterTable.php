<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class MySqlAlterTable implements QueryInterface
{
    /**
     * @param list<MySqlAddColumn> $columnToCreate
     * @param list<MySqlDropColumn> $columnToDrop
     * @param list<MySqlAlterColumn> $columnToAlter
     * @param list<MySqlAddIndex> $indexesToCreate
     * @param list<MySqlDropIndex> $indexesToDrop
     * @param list<MySqlAddForeignKey> $foreignKeysToCreate
     * @param list<MySqlDropForeignKey> $foreignKeysToDrop
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
            EscapeUtils::escape($this->name),
            implode(', ', $this->getQueries()),
        );
    }

    /** @return list<string> */
    private function getQueries(): array
    {
        return array_values(array_filter([
            $this->getAddColumnsQuery(),
            $this->getDropColumnsQuery(),
            $this->getAlterColumnsQuery(),
            $this->getAddIndexesQuery(),
            $this->getDropIndexesQuery(),
            $this->getAddForeignKeysQuery(),
            $this->getDropForeignKeysQuery(),
        ], fn(string $query): bool => $query !== ''));
    }

    private function getAddColumnsQuery(): string
    {
        return implode(', ', array_map(
            fn(MySqlAddColumn $col): string => 'ADD ' . $col->getQuery(),
            $this->columnToCreate,
        ));
    }

    private function getDropColumnsQuery(): string
    {
        return implode(', ', array_map(
            fn(MySqlDropColumn $col): string => $col->getQuery(),
            $this->columnToDrop,
        ));
    }

    private function getAlterColumnsQuery(): string
    {
        return implode(', ', array_map(
            fn(MySqlAlterColumn $col): string => sprintf('CHANGE %s %s', EscapeUtils::escape($col->name), $col->getQuery()),
            $this->columnToAlter,
        ));
    }

    private function getAddIndexesQuery(): string
    {
        return implode(', ', array_map(
            fn(MySqlAddIndex $idx): string => 'ADD ' . $idx->getQuery(),
            $this->indexesToCreate,
        ));
    }

    private function getDropIndexesQuery(): string
    {
        return implode(', ', array_map(
            fn(MySqlDropIndex $idx): string => $idx->getQuery(),
            $this->indexesToDrop,
        ));
    }

    private function getAddForeignKeysQuery(): string
    {
        return implode(', ', array_map(
            fn(MySqlAddForeignKey $fk): string => 'ADD ' . $fk->getQuery(),
            $this->foreignKeysToCreate,
        ));
    }

    private function getDropForeignKeysQuery(): string
    {
        return implode(', ', array_map(
            fn(MySqlDropForeignKey $fk): string => $fk->getQuery(),
            $this->foreignKeysToDrop,
        ));
    }
}
