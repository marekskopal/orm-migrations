<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderInterface;
use Psr\Log\LoggerInterface;

readonly class MigrationManager
{
    public function __construct(
        private DatabaseProviderInterface $databaseProvider,
        private MigrationRepository $migrationRepository,
        private string $path,
        private ?LoggerInterface $logger = null,
    )
    {
    }

    public function runAllMigrations(): void
    {
        $migrationClasses = new MigrationClassProvider($this->path)->getMigrationClasses();
        $unfinishedMigrationClasses = $migrationClasses;

        $this->migrationRepository->createMigrationTable();

        foreach ($this->migrationRepository->getFinishedMigrations() as $finishedMigration) {
            $key = array_find_key(
                $unfinishedMigrationClasses,
                fn(MigrationClass $unfinishedMigrationClass): bool => $unfinishedMigrationClass->class === $finishedMigration['name'],
            );
            unset($unfinishedMigrationClasses[$key]);
        }

        foreach ($unfinishedMigrationClasses as $unfinishedMigrationClass) {
            require_once $unfinishedMigrationClass->file;
            $this->runMigration(new $unfinishedMigrationClass->class($this->databaseProvider));
        }
    }

    public function runMigration(Migration $migration): void
    {
        try {
            $migration->configure();
            $migration->up();
            $this->migrationRepository->insertMigration($migration::class);
        } catch (\Throwable $e) {
            if ($this->logger === null) {
                throw $e;
            }
            $this->logger->error($e->getMessage());
        }
    }
}
