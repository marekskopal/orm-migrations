<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderInterface;
use MarekSkopal\ORM\Schema\Builder\ClassScanner\ClassScanner;
use Nette\Utils\Finder;

readonly class MigrationManager
{
    private MigrationRepository $migrationRepository;

    public function __construct(private DatabaseProviderInterface $databaseProvider, private string $path)
    {
        $this->migrationRepository = new MigrationRepository($databaseProvider->getDatabase()->getPdo());
    }

    public function migrate(): void
    {
        $migrationClasses = $this->getMigrationClasses();
        $unfinishedMigrationClasses = $migrationClasses;

        $this->migrationRepository->createMigrationTable();

        foreach ($this->migrationRepository->getFinishedMigrations() as $finishedMigration) {
            unset($unfinishedMigrationClasses[$finishedMigration['name']]);
        }

        foreach ($unfinishedMigrationClasses as $unfinishedMigrationClass) {
            $this->runMigration(new $unfinishedMigrationClass($this->databaseProvider));
            $this->migrationRepository->insertMigration($unfinishedMigrationClass);
        }
    }

    private function runMigration(Migration $migration): void
    {
        $migration->configure();
        $migration->up();
    }

    /** @return array<class-string<Migration>> */
    private function getMigrationClasses(): array
    {
        $migrationClasses = [];

        $phpFiles = Finder::findFiles($this->path . '/**/*.php');
        foreach ($phpFiles as $phpFile) {
            $classScanner = new ClassScanner($phpFile->getRealPath());
            foreach ($classScanner->findClasses() as $class) {
                if (is_subclass_of($class, Migration::class)) {
                    require_once $phpFile->getRealPath();
                    $migrationClasses[] = $class;
                }
            }
        }

        return $migrationClasses;
    }
}
