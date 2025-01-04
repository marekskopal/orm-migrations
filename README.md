# ORM Migrations

Migration module for MarekSkopalORM library.

Creates migrations from your Entity and database schema with simple API.
Migrations are PHP files and that extends `Migration` class and may contain any of your custom logic.

## Installation

Install via Composer:

```bash
composer require marekskopal/orm-migrations
```

## Usage

### Create Migrator instance

```php

//Create DB connection
$database = new MysqlDatabase('localhost', 'root', 'password', 'database');

//Create schema
$schema = new SchemaBuilder()
    ->addEntityPath(__DIR__ . '/Entity')
    ->build();

//Create Migrator
$pathWithMigrations = __DIR__ . '/migrations/';
$migrator = new Migrator($pathWithMigrations, $database);
```

### Create new migration

```php
$migrator->generate(
    $schema,
    name: 'CreateUserTable',
);
```

This will create new migration PHP file in `__DIR__ . '/migrations/'` directory.

Example of generated migration file:

```php
<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Generator\Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class CreateUserTable extends Migration
{
    public function up(): void
    {
        $this->table('table_a')
            ->addColumn('id', Type::Int, autoincrement: true, primary: true)
            ->addColumn('name', Type::String, nullable: true, size: 255)
            ->addColumn('address', Type::String, size: 50, default: 'New York')
            ->addColumn('score', Type::Int, size: 10)
            ->addColumn('price', Type::Decimal, precision: 10, scale: 2)
            ->addColumn('type', Type::Enum, enum: ['a', 'b', 'c'], default: 'a')
            ->create();
    }

    public function down(): void
    {
        $this->table('table_a')
            ->drop();
    }
}
```

Added, changed or removed tables, columns, indexes and keys are automatically detected from comparing Entity schema with your database.


### Run migrations

```php
$migrator->migrate();
```
