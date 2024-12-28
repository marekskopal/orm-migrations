<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Schema\Provider;

use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\MySqlTypeConverter;
use MarekSkopal\ORM\Migrations\Schema\DatabaseSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Schema\Provider\MySqlSchemaProvider;
use MarekSkopal\ORM\Migrations\Schema\TableSchema;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\ColumnSchemaFixture;
use MarekSkopal\ORM\Migrations\Utils\ArrayUtils;
use MarekSkopal\ORM\Migrations\Utils\ColumnType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MySqlSchemaProvider::class)]
#[UsesClass(MySqlTypeConverter::class)]
#[UsesClass(DatabaseSchema::class)]
#[UsesClass(TableSchema::class)]
#[UsesClass(ColumnSchema::class)]
#[UsesClass(ColumnType::class)]
#[UsesClass(IndexSchema::class)]
#[UsesClass(ForeignKeySchema::class)]
#[UsesClass(ArrayUtils::class)]
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

        $mySqlSchemaProvider = new MySqlSchemaProvider($mySqlDatabase, new MySqlTypeConverter());

        $databaseSchema = $mySqlSchemaProvider->getDatabaseSchema();

        self::assertCount(2, $databaseSchema->tables);
        self::assertArrayHasKey('table_a', $databaseSchema->tables);
        self::assertArrayHasKey('table_b', $databaseSchema->tables);

        self::assertCount(6, $databaseSchema->tables['table_a']->columns);
        self::assertArrayHasKey('id', $databaseSchema->tables['table_a']->columns);
        self::assertObjectEquals(ColumnSchemaFixture::create(
            name: 'id',
            type: Type::Int,
            autoincrement: true,
            primary: true,
            size: 11,
        ), $databaseSchema->tables['table_a']->columns['id']);
        self::assertArrayHasKey('created_at', $databaseSchema->tables['table_a']->columns);
        self::assertObjectEquals(ColumnSchemaFixture::create(
            name: 'created_at',
            type: Type::DateTime,
        ), $databaseSchema->tables['table_a']->columns['created_at']);
        self::assertArrayHasKey('name', $databaseSchema->tables['table_a']->columns);
        self::assertObjectEquals(ColumnSchemaFixture::create(
            name: 'name',
            type: Type::String,
            size: 255,
        ), $databaseSchema->tables['table_a']->columns['name']);
        self::assertArrayHasKey('email', $databaseSchema->tables['table_a']->columns);
        self::assertObjectEquals(ColumnSchemaFixture::create(
            name: 'email',
            type: Type::String,
            nullable: true,
            size: 255,
        ), $databaseSchema->tables['table_a']->columns['email']);
        self::assertArrayHasKey('type', $databaseSchema->tables['table_a']->columns);
        self::assertObjectEquals(ColumnSchemaFixture::create(
            name: 'type',
            type: Type::Enum,
            enum: ['a', 'b', 'c'],
            default: 'a',
        ), $databaseSchema->tables['table_a']->columns['type']);
        self::assertArrayHasKey('price', $databaseSchema->tables['table_a']->columns);
        self::assertObjectEquals(ColumnSchemaFixture::create(
            name: 'price',
            type: Type::Decimal,
            precision: 11,
            scale: 2,
        ), $databaseSchema->tables['table_a']->columns['price']);

        self::assertCount(0, $databaseSchema->tables['table_a']->indexes);

        self::assertCount(0, $databaseSchema->tables['table_a']->foreignKeys);

        self::assertCount(2, $databaseSchema->tables['table_b']->columns);
        self::assertArrayHasKey('id', $databaseSchema->tables['table_b']->columns);
        self::assertArrayHasKey('table_a_id', $databaseSchema->tables['table_b']->columns);

        self::assertCount(1, $databaseSchema->tables['table_b']->indexes);

        self::assertCount(1, $databaseSchema->tables['table_b']->foreignKeys);
    }
}
