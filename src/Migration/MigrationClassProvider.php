<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use MarekSkopal\ORM\Schema\Builder\ClassScanner\ClassScanner;
use Nette\Utils\Finder;

final readonly class MigrationClassProvider
{
    public function __construct(private string $path)
    {
    }

    /** @return list<MigrationClass> */
    public function getMigrationClasses(): array
    {
        $migrationClasses = [];

        $phpFiles = Finder::findFiles(rtrim($this->path, '/') . '/**/*.php');
        foreach ($phpFiles as $phpFile) {
            $classScanner = new ClassScanner($phpFile->getRealPath());
            // Classes are validated as Migration subclasses in MigrationManager,
            // after the file is loaded (is_subclass_of requires the class to exist).
            foreach ($classScanner->findClasses() as $class) {
                $migrationClasses[] = new MigrationClass($class, $phpFile->getRealPath());
            }
        }

        usort($migrationClasses, fn(MigrationClass $a, MigrationClass $b) => basename($a->file) <=> basename($b->file));

        return $migrationClasses;
    }
}
