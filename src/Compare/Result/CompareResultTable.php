<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

readonly class CompareResultTable
{
    /**
     * @param list<CompareResultColumn> $columnsToCreate
     * @param list<CompareResultColumn> $columnsToDrop
     * @param list<CompareResultColumn> $columnsToAlter
     * @param list<CompareResultIndex> $indexToCreate
     * @param list<CompareResultIndex> $indexToDrop
     * @param list<CompareResultForeignKey> $foreignKeyToCreate
     * @param list<CompareResultForeignKey> $foreignKeyToDrop
     */
    public function __construct(
        public string $name,
        public array $columnsToCreate,
        public array $columnsToDrop,
        public array $columnsToAlter,
        public array $indexToCreate,
        public array $indexToDrop,
        public array $foreignKeyToCreate,
        public array $foreignKeyToDrop,
    ) {
    }
}
