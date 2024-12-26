<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

readonly class CompareResultTable
{
    /**
     * @param list<CompareResultColumn> $columnsToCreate
     * @param list<CompareResultColumn> $columnsToDrop
     * @param list<CompareResultColumn> $columnsToAlter
     * @param list<CompareResultIndex> $indexesToCreate
     * @param list<CompareResultIndex> $indexesToDrop
     * @param list<CompareResultForeignKey> $foreignKeysToCreate
     * @param list<CompareResultForeignKey> $foreignKeysToDrop
     */
    public function __construct(
        public string $name,
        public array $columnsToCreate,
        public array $columnsToDrop,
        public array $columnsToAlter,
        public array $indexesToCreate,
        public array $indexesToDrop,
        public array $foreignKeysToCreate,
        public array $foreignKeysToDrop,
    ) {
    }
}
