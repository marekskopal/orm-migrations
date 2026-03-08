<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Database\Provider;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Database\PostgresDatabase;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlQueryFactory;
use MarekSkopal\ORM\Migrations\Migration\Query\Pgsql\PgsqlQueryFactory;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\MySqlTypeConverter;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\PgsqlTypeConverter;
use MarekSkopal\ORM\Migrations\Schema\Provider\MySqlSchemaProvider;
use MarekSkopal\ORM\Migrations\Schema\Provider\PgsqlSchemaProvider;

final class DatabaseProviderFactory
{
    public function create(DatabaseInterface $database): DatabaseProviderInterface
    {
        return match (true) {
            $database instanceof MySqlDatabase => new DatabaseProvider(
                database: $database,
                schemaProvider: new MySqlSchemaProvider($database, new MySqlTypeConverter()),
                typeConverter: new MySqlTypeConverter(),
                queryFactory: new MySqlQueryFactory(),
            ),
            $database instanceof PostgresDatabase => new DatabaseProvider(
                database: $database,
                schemaProvider: new PgsqlSchemaProvider($database, new PgsqlTypeConverter()),
                typeConverter: new PgsqlTypeConverter(),
                queryFactory: new PgsqlQueryFactory(),
            ),
            default => throw new \InvalidArgumentException('Unsupported database type'),
        };
    }
}
