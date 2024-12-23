<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

readonly class CompareResultTable
{
    /**
     * @param list<CompareResultColumn> $columnsToCreate
     * @param list<CompareResultColumn> $columnsToDrop
     * @param list<CompareResultColumn> $columnsToAlter
     */
    public function __construct(
        public string $name,
        public array $columnsToCreate,
        public array $columnsToDrop,
        public array $columnsToAlter,
    ) {
    }
}
