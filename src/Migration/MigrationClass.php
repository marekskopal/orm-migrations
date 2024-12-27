<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

final readonly class MigrationClass
{
    /** @param class-string<Migration> $class */
    public function __construct(public string $class, public string $file)
    {
    }
}
