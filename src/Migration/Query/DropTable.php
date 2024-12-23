<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

readonly class DropTable implements QueryInterface
{
    public function __construct(public string $name)
    {
    }

    public function getQuery(): string
    {
        return sprintf('DROP TABLE %s;', $this->name);
    }
}
