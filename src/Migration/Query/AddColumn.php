<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Utils\NameUtils;

readonly class AddColumn implements QueryInterface
{
    /** @param list<string>|null $enum */
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable = false,
        public bool $autoincrement = false,
        public bool $primary = false,
        public ?int $size = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public ?array $enum = null,
        public string|int|float|null $default = null,
    ) {
    }

    public function getQuery(): string
    {
        $type = strtoupper($this->type);
        if ($this->size !== null) {
            $type .= '(' . $this->size . ')';
        } elseif ($this->precision !== null && $this->scale !== null) {
            $type .= sprintf('(%d,%d)', $this->precision, $this->scale);
        } elseif ($this->enum !== null) {
            $type .= sprintf('(%s)', implode(',', array_map(fn (string $value): string => sprintf('"%s"', $value), $this->enum)));
        }

        $query = sprintf('%s %s', NameUtils::escape($this->name), $type);

        if ($this->nullable) {
            $query .= ' NULL';
        } else {
            $query .= ' NOT NULL';
        }

        if ($this->autoincrement) {
            $query .= ' AUTO_INCREMENT';
        }

        if ($this->primary) {
            $query .= ' PRIMARY KEY';
        }

        if ($this->default !== null) {
            $query .= sprintf(' DEFAULT "%s"', (string) $this->default);
        } elseif ($this->nullable) {
            $query .= ' DEFAULT NULL';
        }

        return $query;
    }
}
