<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Database\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\MySqlTypeConverter;
use MarekSkopal\ORM\Migrations\Schema\Provider\MySqlSchemaProvider;

final class DatabaseProviderFactory
{
    public function create(DatabaseInterface $database): DatabaseProviderInterface
    {
        return match (true) {
            $database instanceof MySqlDatabase => new DatabaseProvider(
                database: $database,
                schemaProvider: new MySqlSchemaProvider($database, new MySqlTypeConverter()),
                typeConverter: new MySqlTypeConverter(),
            ),
            default => throw new \InvalidArgumentException('Unsupported database type'),
        };
    }
}
