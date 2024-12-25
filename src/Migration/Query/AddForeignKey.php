<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration\Query;

use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;
use MarekSkopal\ORM\Utils\NameUtils;

readonly class AddForeignKey implements QueryInterface
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
            $this->name !== null ? ('CONSTRAINT ' . NameUtils::escape($this->name) . ' ') : '',
            NameUtils::escape($this->column),
            NameUtils::escape($this->referenceTable),
            NameUtils::escape($this->referenceColumn),
            $this->onDelete->value,
            $this->onUpdate->value,
        );
    }
}
