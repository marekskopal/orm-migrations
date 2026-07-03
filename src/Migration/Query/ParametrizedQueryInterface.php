<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

interface ParametrizedQueryInterface extends QueryInterface
{
    /**
     * Values to bind to the placeholders returned by {@see QueryInterface::getQuery()}.
     *
     * @return list<string|int|float|null>
     */
    public function getParameters(): array;
}
