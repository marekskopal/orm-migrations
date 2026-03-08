<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class PgsqlDropColumn implements QueryInterface
{
    public function __construct(public string $name)
    {
    }

    public function getQuery(): string
    {
        return sprintf('DROP COLUMN %s', EscapeUtils::escape($this->name, '"'));
    }
}
