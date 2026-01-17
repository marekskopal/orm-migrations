<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use BackedEnum;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use MarekSkopal\ORM\Utils\NameUtils;

readonly class Insert implements QueryInterface
{
    /** @param array<array<string|int|float|bool|BackedEnum|null>> $values */
    public function __construct(public string $name, public array $values)
    {
    }

    public function getQuery(): string
    {
        return sprintf(
            'INSERT INTO %s (%s) VALUES %s;',
            NameUtils::escape($this->name),
            $this->getColunsQuery(),
            $this->getValuesQuery(),
        );
    }

    private function getColunsQuery(): string
    {
        return implode(', ', array_map(fn(string $column): string => NameUtils::escape($column), array_keys($this->values[0])));
    }

    private function getValuesQuery(): string
    {
        return implode(
            ', ',
            array_map(
                fn(array $row): string => sprintf(
                    '(%s)',
                    implode(', ', array_map(fn(string|int|float|bool|BackedEnum|null $value): string => StringUtils::toSql($value), $row)),
                ),
                $this->values,
            ),
        );
    }
}
