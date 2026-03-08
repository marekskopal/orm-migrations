# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/Path/To/TestFile.php

# Run a single test method
./vendor/bin/phpunit --filter testMethodName

# Static analysis
./vendor/bin/phpstan analyse

# Code style check
./vendor/bin/phpcs

# Code style fix
./vendor/bin/phpcbf
```

Tests require a MySQL database. Credentials are configured in `phpunit.xml` (host: localhost, database: orm_test, user: marek).

## Architecture

This is a PHP 8.4 library for generating and running database migrations, integrated with the [MarekSkopal ORM](https://github.com/marekskopal/orm) framework.

### Entry Point

`Migrator` (`src/Migrator.php`) is the main API class with two public methods:
- `generate(Schema $schema, string $name, string $namespace): void` — compares ORM schema with DB and generates migration files
- `migrate(): void` — executes all pending migrations

### Core Flow

**Migration Generation:**
`Migrator::generate()` → `OrmSchemaConverter` (ORM entities → `DatabaseSchema`) + `DatabaseProviderFactory` + `MySqlSchemaProvider` (reads live DB schema) → `SchemaComparator` (diffs the two schemas → `CompareResult`) → `MigrationGenerator` (writes PHP files using `nette/php-generator`)

**Migration Execution:**
`Migrator::migrate()` → `MigrationRepository` (manages `__migrations` tracking table) → `MigrationManager` → `MigrationClassProvider` (discovers and sorts files by timestamp prefix) → executes each `Migration::up()` via `TableBuilder` → Query classes → PDO

### Key Layers

| Layer | Location | Purpose |
|---|---|---|
| Schema definitions | `src/Schema/` | `DatabaseSchema`, `TableSchema`, `ColumnSchema`, `IndexSchema`, `ForeignKeySchema` |
| Comparison | `src/Compare/` | `SchemaComparator` diffs two `DatabaseSchema` objects into `CompareResult` |
| Generation | `src/Generator/` | `MigrationGenerator` emits PHP migration classes |
| Migration execution | `src/Migration/` | `Migration` base class, `TableBuilder` fluent API, Query classes |
| Database providers | `src/Database/` | `DatabaseProviderFactory`, `MySqlSchemaProvider`, `MySqlTypeConverter` |
| Utilities | `src/Utils/` | `ColumnType` parser, `StringUtils`, `ArrayUtils`, `EnumUtils` |

### Migration Files

Generated migration filenames use `Ymd_His_` timestamp prefix (e.g. `20240101_120000_CreateUserTable.php`). `MigrationClassProvider` sorts files by this prefix to ensure correct execution order.

Migrations extend the abstract `Migration` class and implement `up()` and `down()` methods using the fluent `TableBuilder` API (accessed via `$this->table('name')`).

### Database Support

Currently only MySQL is implemented. Adding a new database requires implementing `DatabaseProviderInterface` and a corresponding schema provider and type converter.

### Type System

ORM types (`MarekSkopal\ORM\Enum\Type`) are mapped to/from MySQL column types by `MySqlTypeConverter`. `ColumnType` utility parses raw MySQL type strings (e.g. `varchar(255)`, `decimal(10,2)`, `enum('a','b','c')`) into structured data.
