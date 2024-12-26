<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Schema\Provider;

use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Schema\Provider\MySqlSchemaProvider;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Utils\ColumnType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlSchemaProvider::class)]
#[UsesClass(DatabaseSchema::class)]
#[UsesClass(TableSchema::class)]
#[UsesClass(ColumnSchema::class)]
#[UsesClass(ColumnType::class)]
#[UsesClass(IndexSchema::class)]
#[UsesClass(ForeignKeySchema::class)]
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
        self::assertArrayHasKey('table_a', $databaseSchema->tables);
        self::assertArrayHasKey('table_b', $databaseSchema->tables);

        self::assertCount(6, $databaseSchema->tables['table_a']->columns);
        self::assertArrayHasKey('id', $databaseSchema->tables['table_a']->columns);
        self::assertArrayHasKey('created_at', $databaseSchema->tables['table_a']->columns);
        self::assertArrayHasKey('name', $databaseSchema->tables['table_a']->columns);
        self::assertArrayHasKey('email', $databaseSchema->tables['table_a']->columns);
        self::assertArrayHasKey('type', $databaseSchema->tables['table_a']->columns);
        self::assertArrayHasKey('price', $databaseSchema->tables['table_a']->columns);

        self::assertCount(0, $databaseSchema->tables['table_a']->indexes);

        self::assertCount(0, $databaseSchema->tables['table_a']->foreignKeys);

        self::assertCount(2, $databaseSchema->tables['table_b']->columns);
        self::assertArrayHasKey('id', $databaseSchema->tables['table_b']->columns);
        self::assertArrayHasKey('table_a_id', $databaseSchema->tables['table_b']->columns);

        self::assertCount(1, $databaseSchema->tables['table_b']->indexes);

        self::assertCount(1, $databaseSchema->tables['table_b']->foreignKeys);
    }
}
