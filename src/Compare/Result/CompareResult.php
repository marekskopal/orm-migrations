<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Compare\Result;

readonly class CompareResult
{
    /**
     * @param list<CompareResultTable> $tablesToCreate
     * @param list<CompareResultTable> $tablesToDrop
     * @param list<CompareResultTable> $tablesToAlter
     */
    public function __construct(public array $tablesToCreate, public array $tablesToDrop, public array $tablesToAlter)
    {
    }
}
