<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

interface QueryInterface
{
    public function getQuery(): string;
}
