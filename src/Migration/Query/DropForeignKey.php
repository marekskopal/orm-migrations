<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Utils\NameUtils;

readonly class DropForeignKey implements QueryInterface
{
    public function __construct(public string $name,)
    {
    }

    public function getQuery(): string
    {
        return sprintf('DROP FOREIGN KEY %s', NameUtils::escape($this->name));
    }
}
