<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Utils\NameUtils;

readonly class AddIndex implements QueryInterface
{
    /** @param list<string> $columns */
    public function __construct(public string $table, public array $columns, public string $name, public bool $unique,)
    {
    }

    public function getQuery(): string
    {
        $query = sprintf(
            'CREATE %sINDEX %s ON %s (%s)',
            $this->unique ? 'UNIQUE ' : '',
            NameUtils::escape($this->name),
            NameUtils::escape($this->table),
            implode(', ', array_map(fn($column) => NameUtils::escape($column), $this->columns)),
        );

        return $query;
    }
}
