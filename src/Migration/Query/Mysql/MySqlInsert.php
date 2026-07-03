<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Mysql;

use BackedEnum;
use MarekSkopal\ORM\Migrations\Migration\Query\ParametrizedQueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;

readonly class MySqlInsert implements ParametrizedQueryInterface
{
    /** @param array<array<string|int|float|bool|BackedEnum|null>> $values */
    public function __construct(public string $name, public array $values)
    {
    }

    public function getQuery(): string
    {
        return sprintf(
            'INSERT INTO %s (%s) VALUES %s;',
            EscapeUtils::escape($this->name),
            $this->getColumnsQuery(),
            $this->getValuesQuery(),
        );
    }

    private function getColumnsQuery(): string
    {
        return implode(', ', array_map(fn(string $column): string => EscapeUtils::escape($column), array_keys($this->values[0])));
    }

    private function getValuesQuery(): string
    {
        return implode(
            ', ',
            array_map(
                fn(array $row): string => sprintf('(%s)', implode(', ', array_fill(0, count($row), '?'))),
                $this->values,
            ),
        );
    }

    /** @return list<string|int|float|null> */
    public function getParameters(): array
    {
        $parameters = [];
        foreach ($this->values as $row) {
            foreach ($row as $value) {
                $parameters[] = StringUtils::toParameter($value);
            }
        }

        return $parameters;
    }
}
