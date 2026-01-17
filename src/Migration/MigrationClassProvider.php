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
            /** @var class-string<Migration> $class */
            foreach ($classScanner->findClasses() as $class) {
                //if (!is_subclass_of($class, Migration::class)) {
                //    continue;
                //}

                $migrationClasses[] = new MigrationClass($class, $phpFile->getRealPath());
            }
        }

        usort($migrationClasses, fn(MigrationClass $a, MigrationClass $b) => basename($a->file) <=> basename($b->file));

        return $migrationClasses;
    }
}
