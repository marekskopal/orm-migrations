<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Schema\Provider;

use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\Provider\MySqlSchemaProvider;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlSchemaProvider::class)]
#[UsesClass(DatabaseSchema::class)]
#[UsesClass(TableSchema::class)]
#[UsesClass(ColumnSchema::class)]
final class MySqlSchemaProviderTest extends TestCase
{
    public function testGetDatabaseSchema(): void
    {
        $mySqlDatabase = new MySqlDatabase(
            host: (string) getenv('MYSQL_HOST'),
            username: (string) getenv('MYSQL_USER'),
            password: (string) getenv('MYSQL_PASSWORD'),
            database: (string) getenv('MYSQL_DATABASE'),
        );

        $mySqlSchemaProvider = new MySqlSchemaProvider();

        $databaseSchema = $mySqlSchemaProvider->getDatabaseSchema($mySqlDatabase);
        self::assertCount(2, $databaseSchema->tables);
    }
}
