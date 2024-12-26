<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Schema;

readonly class IndexSchema
{
    /** @param list<string> $columns */
    public function __construct(public array $columns, public string $name, public bool $unique,)
    {
    }
}
