<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class PgsqlCreateIndex implements QueryInterface
{
    /** @param list<string> $columns */
    public function __construct(
        public array $columns,
        public string $name,
        public bool $unique,
        public string $tableName,
    ) {
    }

    public function getQuery(): string
    {
        return sprintf(
            'CREATE %sINDEX %s ON %s (%s);',
            $this->unique ? 'UNIQUE ' : '',
            EscapeUtils::escape($this->name, '"'),
            EscapeUtils::escape($this->tableName, '"'),
            implode(', ', array_map(fn(string $col): string => EscapeUtils::escape($col, '"'), $this->columns)),
        );
    }
}
