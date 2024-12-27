<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Compare\SchemaComparator;
use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderFactory;
use MarekSkopal\ORM\Migrations\Generator\MigrationGenerator;
use MarekSkopal\ORM\Migrations\Migration\MigrationManager;
use MarekSkopal\ORM\Migrations\Schema\Converter\OrmSchemaConverter;
use MarekSkopal\ORM\Schema\Schema;

readonly class Migrator
{
    public function __construct(private string $path, private DatabaseInterface $database,)
    {
    }

    public function generate(Schema $schema, string $name = 'Migration', string $namespace = 'Migrations'): void
    {
        $schemaComparator = new SchemaComparator();

        if (!($this->database instanceof MySqlDatabase)) {
            throw new \Exception('Unsupported database');
        }

        $databaseProvider = new DatabaseProviderFactory()->create($this->database);
        $databaseSchema = $databaseProvider->getSchemaProvider()->getDatabaseSchema();

        $compareResult = $schemaComparator->compare(
            $databaseSchema,
            new OrmSchemaConverter()->convert($schema),
        );

        $migrationGenerator = new MigrationGenerator($this->path);
        $migrationGenerator->generate($compareResult, $name, $namespace);
    }

    public function migrate(): void
    {
        $databaseProvider = new DatabaseProviderFactory()->create($this->database);

        $migrationManager = new MigrationManager($databaseProvider, $this->path);
        $migrationManager->migrate();
    }
}
