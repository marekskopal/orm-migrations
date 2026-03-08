# ORM Migrations

Database migration library for [marekskopal/orm](https://github.com/marekskopal/orm). Automatically generates PHP migration files by comparing your ORM entity schema with the live database schema.

Supports **MySQL** and **PostgreSQL**.

## Installation

```bash
composer require marekskopal/orm-migrations
```

## Usage

### Setup

```php
use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Migrator;
use MarekSkopal\ORM\Schema\Builder\SchemaBuilder;

$database = new MySqlDatabase(host: 'localhost', username: 'root', password: 'password', database: 'mydb');

$schema = (new SchemaBuilder())
    ->addEntityPath(__DIR__ . '/Entity')
    ->build();

$migrator = new Migrator(
    path: __DIR__ . '/migrations/',
    database: $database,
);
```

For PostgreSQL, use `PostgresDatabase` instead of `MySqlDatabase` — no other changes needed.

An optional PSR-3 `LoggerInterface` can be passed as the third argument to `Migrator` to log migration execution.

### Generate a migration

```php
$migrator->generate(schema: $schema, name: 'CreateUserTable', namespace: 'App\Migrations');
```

This compares the ORM entity schema against the live database and writes a new PHP migration file to the configured path. Only actual differences (added/changed/removed tables, columns, indexes, foreign keys) are included.

Example generated file:

```php
<?php

declare(strict_types=1);

namespace App\Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class CreateUserTable extends Migration
{
    public function up(): void
    {
        $this->table('user')
            ->addColumn('id', Type::Int, autoincrement: true, primary: true)
            ->addColumn('name', Type::String, size: 255)
            ->addColumn('email', Type::String, nullable: true, size: 255)
            ->addColumn('score', Type::Decimal, precision: 10, scale: 2)
            ->addColumn('role', Type::Enum, enum: ['admin', 'user'], default: 'user')
            ->addIndex(['email'], name: 'idx_user_email', unique: true)
            ->create();
    }

    public function down(): void
    {
        $this->table('user')->drop();
    }
}
```

### Run migrations

```php
$migrator->migrate();
```

Executes all pending migrations in order. Completed migrations are tracked in a `__migrations` table in the database.

## TableBuilder API

Inside `up()` and `down()`, use `$this->table(string $name)` to get a `TableBuilder`. All methods are fluent and support both `Type` enum values and their string names.

### Column operations

```php
$this->table('post')
    ->addColumn('id', Type::Int, autoincrement: true, primary: true)
    ->addColumn('title', Type::String, size: 255)
    ->addColumn('body', Type::Text, nullable: true)
    ->addColumn('published', Type::Boolean, default: false)
    ->addColumn('price', Type::Decimal, precision: 10, scale: 2)
    ->addColumn('status', Type::Enum, enum: ['draft', 'published'], default: 'draft')
    ->create();

// Modify an existing table
$this->table('post')
    ->addColumn('views', Type::Int, default: 0)
    ->alterColumn('title', Type::String, size: 500)
    ->dropColumn('legacy_field')
    ->alter();
```

### Index operations

```php
$this->table('post')
    ->addIndex(['slug'], name: 'idx_post_slug', unique: true)
    ->addIndex(['author_id', 'created_at'], name: 'idx_post_author_date', unique: false)
    ->dropIndex('idx_old_index')
    ->alter();
```

### Foreign key operations

```php
use MarekSkopal\ORM\Migrations\Migration\Query\Enum\ReferenceOptionEnum;

$this->table('post')
    ->addForeignKey(
        column: 'author_id',
        referenceTable: 'user',
        referenceColumn: 'id',
        name: 'fk_post_author',
        onDelete: ReferenceOptionEnum::Cascade,
        onUpdate: ReferenceOptionEnum::Cascade,
    )
    ->dropForeignKey('fk_old_key')
    ->alter();
```

### Insert data

```php
$this->table('role')->insert([
    ['name' => 'admin', 'label' => 'Administrator'],
    ['name' => 'user',  'label' => 'Standard User'],
]);
```

### Available column types

| `Type` enum | MySQL | PostgreSQL |
|---|---|---|
| `Type::SmallInt` | `smallint` | `smallint` |
| `Type::Int` | `int` | `integer` |
| `Type::BigInt` | `bigint` | `bigint` |
| `Type::Decimal` | `decimal` | `decimal` |
| `Type::Float` | `float` | `real` |
| `Type::Double` | `double precision` | `double precision` |
| `Type::String` | `varchar` | `varchar` |
| `Type::Text` | `text` | `text` |
| `Type::Boolean` | `tinyint(1)` | `boolean` |
| `Type::Uuid` | `uuid` | `uuid` |
| `Type::Date` | `date` | `date` |
| `Type::DateTime` | `datetime` | `timestamp` |
| `Type::Timestamp` | `timestamp` | `timestamptz` |
| `Type::Json` | `json` | `json` |
| `Type::Enum` | `enum(...)` | `varchar` + `CHECK` |

## Migration file naming

Generated files use a `Ymd_His_` timestamp prefix (e.g. `20240101_120000_CreateUserTable.php`). Migrations are executed in filename order, so this ensures correct sequencing even across branches.

## License

MIT
