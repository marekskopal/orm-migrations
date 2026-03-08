<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Mysql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class MySqlDropIndex implements QueryInterface
{
    public function __construct(public string $name)
    {
    }

    public function getQuery(): string
    {
        return sprintf('DROP INDEX %s', EscapeUtils::escape($this->name));
    }
}
