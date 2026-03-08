<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class AddIndex implements QueryInterface
{
    /** @param list<string> $columns */
    public function __construct(public array $columns, public string $name, public bool $unique,)
    {
    }

    public function getQuery(): string
    {
        $query = sprintf(
            '%sINDEX %s (%s)',
            $this->unique ? 'UNIQUE ' : '',
            EscapeUtils::escape($this->name),
            implode(', ', array_map(fn($column) => EscapeUtils::escape($column), $this->columns)),
        );

        return $query;
    }
}
