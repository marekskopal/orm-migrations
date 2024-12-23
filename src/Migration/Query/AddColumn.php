<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Utils\NameUtils;

readonly class AddColumn implements QueryInterface
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable = false,
        public bool $autoincrement = false,
        public bool $primary = false,
        public string|int|float|null $default = null,
    ) {
    }

    public function getQuery(): string
    {
        $query = sprintf('%s %s', NameUtils::escape($this->name), $this->type);

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
