<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

final readonly class MigrationClass
{
    /**
     * @param class-string $class A class discovered on disk; not guaranteed to be a
     *     Migration subclass until validated in MigrationManager.
     */
    public function __construct(public string $class, public string $file)
    {
    }
}
