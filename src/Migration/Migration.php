<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderInterface;

abstract class Migration
{
    public function __construct(protected readonly DatabaseProviderInterface $databaseProvider)
    {
    }

    public function configure(): void
    {
    }

    abstract public function up(): void;

    abstract public function down(): void;

    protected function table(string $name): TableBuilder
    {
        return new TableBuilder($this->databaseProvider, $name);
    }
}
