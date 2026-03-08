<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class MySqlAddIndex implements QueryInterface
{
    /** @param list<string> $columns */
    public function __construct(public array $columns, public string $name, public bool $unique)
    {
    }

    public function getQuery(): string
    {
        return sprintf(
            '%sINDEX %s (%s)',
            $this->unique ? 'UNIQUE ' : '',
            EscapeUtils::escape($this->name),
            implode(', ', array_map(fn(string $column): string => EscapeUtils::escape($column), $this->columns)),
        );
    }
}
