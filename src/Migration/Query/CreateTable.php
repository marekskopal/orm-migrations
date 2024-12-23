<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Utils\NameUtils;

readonly class CreateTable implements QueryInterface
{
    /**
     * @param string $name
     * @param list<AddColumn> $columns
     */
    public function __construct(public string $name, public array $columns)
    {
    }

    public function getQuery(): string
    {
        return sprintf('CREATE TABLE %s (%s);', NameUtils::escape($this->name), $this->getColumnsQuery());
    }

    private function getColumnsQuery(): string
    {
        $columns = [];

        foreach ($this->columns as $column) {
            $columns[] = $column->getQuery();
        }

        return implode(', ', $columns);
    }
}
