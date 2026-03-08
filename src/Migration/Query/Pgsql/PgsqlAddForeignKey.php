<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query\Pgsql;

use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use MarekSkopal\ORM\Migrations\Migration\Query\QueryInterface;
use MarekSkopal\ORM\Migrations\Utils\EscapeUtils;

readonly class PgsqlAddForeignKey implements QueryInterface
{
    public function __construct(
        public string $column,
        public string $referenceTable,
        public string $referenceColumn,
        public ?string $name = null,
        public ReferenceOptionEnum $onDelete = ReferenceOptionEnum::Cascade,
        public ReferenceOptionEnum $onUpdate = ReferenceOptionEnum::Cascade,
    ) {
    }

    public function getQuery(): string
    {
        return sprintf(
            '%sFOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s ON UPDATE %s',
            $this->name !== null ? ('CONSTRAINT ' . EscapeUtils::escape($this->name, '"') . ' ') : '',
            EscapeUtils::escape($this->column, '"'),
            EscapeUtils::escape($this->referenceTable, '"'),
            EscapeUtils::escape($this->referenceColumn, '"'),
            $this->onDelete->value,
            $this->onUpdate->value,
        );
    }
}
