# Security Audit — marekskopal/orm-migrations

**Date:** 2026-07-03
**Scope:** `src/` (all query builders, schema providers, code generator, migration runner, utilities), `phpunit.xml`, CI config.
**Reviewer:** Claude Code

## Threat model

This is a developer tool. It runs in two modes:

- **`generate()`** — reads the live database schema and the ORM entity schema, diffs them, and *writes a PHP migration file* to disk.
- **`migrate()`** — discovers migration classes on disk, instantiates them, and executes their SQL against the database via PDO.

The library performs **all** SQL identifier quoting and value quoting itself (`EscapeUtils`, `StringUtils`). `PDO::quote()` / prepared-statement placeholders are used only in a few internal reads — never for identifiers, DEFAULT/ENUM literals, or `insert()` data. That makes the string-assembly code the primary attack surface.

"Attacker-controlled" input reaches these sinks through three realistic channels:

1. **Data-migration values** passed to `TableBuilder::insert()` — frequently runtime/user-derived data (seed data, backfills copied from another source).
2. **Live-DB schema metadata** (column defaults, enum members, table/column names) read back during `generate()` — trusted less than you'd think if the DB is shared, restored from a dump, or previously tampered with.
3. **ORM entity definitions** — developer-controlled, but a wrong assumption here still produces broken/injectable SQL from a stray quote in a default value.

Findings are ordered by severity.

---

## HIGH-1 — SQL injection in `INSERT` values (no value escaping)

**Files:** `src/Utils/StringUtils.php:39-49`, `src/Migration/Query/Mysql/MySqlInsert.php:41`, `src/Migration/Query/Pgsql/PgsqlInsert.php:41`

`StringUtils::toSql()` wraps every string value in double quotes with **zero escaping**:

```php
is_string($value) => '"' . $value . '"',
```

Both `MySqlInsert` and `PgsqlInsert` build their `VALUES (...)` clause by mapping each row value through `toSql()`, and the resulting SQL is handed straight to `PDO::exec()` in `TableBuilder::executeQuery()` (`src/Migration/TableBuilder.php:174-175`). No prepared statement, no `PDO::quote()`.

**Failure scenario:** a data migration does
```php
$this->table('users')->insert([['name' => $importedName]]);
```
with `$importedName = '"); DROP TABLE users; --'`. The emitted SQL becomes
```sql
INSERT INTO `users` (`name`) VALUES (""); DROP TABLE users; --");
```
Because `exec()` runs the whole string, and MySQL's default PDO driver (with `PDO::MYSQL_ATTR_MULTI_STATEMENTS` on / emulated prepares) executes stacked statements, this is arbitrary SQL execution. Even without stacking, a single `"` corrupts every insert of data containing quotes (names like `O"Brien`, JSON blobs, base64 with `+/=`).

Note also `toSql()` uses `"` for string literals even on PostgreSQL, where `"` is an *identifier* quote — string values there must use `'`. So `PgsqlInsert` produces invalid SQL for any string value regardless of injection.

**Fix:** do not hand-quote values. Route `insert()` through a prepared statement with `?` placeholders and bind the row values (the same pattern already used correctly in `MigrationRepository::insertMigration`). If a literal must be inlined, use `PDO::quote()` with the correct driver.

---

## HIGH-2 — Identifier "escaping" does not escape (`EscapeUtils::escape`)

**File:** `src/Utils/EscapeUtils.php:9-12`

```php
public static function escape(string $name, string $quoteChar = '`'): string
{
    return $quoteChar . $name . $quoteChar;
}
```

This wraps an identifier in quote characters but never doubles/escapes an embedded quote char. Every identifier in the library — table names, column names, index names, foreign-key names, referenced tables/columns — flows through this and then into `PDO::exec()`. Callers are pervasive: `MySqlCreateTable`, `MySqlAlterTable` (incl. the `CHANGE %s` clause at `MySqlAlterTable.php`), all `Add/Drop*` query classes, and the PostgreSQL equivalents, plus `MigrationRepository::createMigrationTable/getFinishedMigrations`.

**Failure scenario:** a column or table name containing a backtick (MySQL) or double quote (PostgreSQL) — e.g. an enum-backed column mapped from an entity whose name is programmatically derived, or a table name read from a tampered/shared live DB during `generate()`:
```
name = "id` , ADD COLUMN evil INT); DROP TABLE users; -- "
```
produces
```sql
ALTER TABLE `t` CHANGE `id` , ADD COLUMN evil INT); DROP TABLE users; -- ` ...
```
Identifier injection → arbitrary DDL. Lower likelihood than HIGH-1 because identifiers are usually developer-controlled, but the function is *named* `escape` and provides a false guarantee that the rest of the codebase relies on.

**Fix:** escape the quote character by doubling it before wrapping, and reject NUL bytes:
```php
return $quoteChar . str_replace($quoteChar, $quoteChar . $quoteChar, $name) . $quoteChar;
```
(MySQL doubles backticks; PostgreSQL/ANSI doubles double-quotes — both use the "double the delimiter" rule, so this single change is correct for both callers.)

---

## HIGH-3 — Code injection into generated migration files (`StringUtils::toCode`)

**Files:** `src/Utils/StringUtils.php:12-23`, `src/Generator/MigrationGenerator.php` (all `toCode` call sites, e.g. lines 163, 192, 196)

`toCode()` emits PHP source for the generated migration, quoting strings as:

```php
is_string($value) => '\'' . $value . '\'',
```

No escaping of `'` or `\`. The values passed in include **column defaults and enum members read from the live database** (`MigrationGenerator::changeColumnToMethodBody` → `default:`/`enum:`), as well as table/column/index/FK names. Those DB-derived strings are attacker-influenceable per the threat model.

**Failure scenario:** a column in the live DB has default value `foo').update(); system('rm -rf /'); //`. During `generate()` this is interpolated verbatim into the generated `.php` file, which is then `require_once`'d and executed by `MigrationManager::runAllMigrations()` (`src/Migration/MigrationManager.php:39`). Result: arbitrary PHP execution the next time migrations run. Even the benign case — a default value containing a single apostrophe — produces a syntactically broken, non-loadable migration file.

**Fix:** never hand-build PHP literals. `nette/php-generator` (already a dependency) provides `Nette\PhpGenerator\Literal` and `Dumper::dump()` for safely rendering values; use those, or at minimum `var_export()`, instead of string concatenation.

---

## MEDIUM-1 — Unescaped DEFAULT and ENUM literals in column definitions

**Files:** `src/Migration/Query/Mysql/MySqlChangeColumn.php:35,55`, `src/Migration/Query/Pgsql/PgsqlChangeColumn.php:58,68`

Column DDL inlines both the DEFAULT value and each ENUM member with a bare `sprintf`, no escaping:

```php
// MySqlChangeColumn
$type .= sprintf('(%s)', implode(',', array_map(fn(string $v) => sprintf('"%s"', $v), $this->enum)));
...
$query .= sprintf(' DEFAULT "%s"', (string) (...$this->default...));
```
```php
// PgsqlChangeColumn
$query .= sprintf(" DEFAULT '%s'", ...);
$query .= sprintf(' CHECK (%s IN (%s))', ..., implode(', ', array_map(fn($v) => sprintf("'%s'", $v), $this->enum)));
```

These strings originate from ORM entity metadata and, during round-tripping, from the live DB. A default or enum member containing the relevant quote character breaks out of the literal into the surrounding DDL statement executed via `exec()`.

**Failure scenario:** an entity/enum with a member value `a','b'); DROP TABLE x; --` or a string default containing `"` yields injectable or malformed `CREATE/ALTER TABLE` SQL.

**Fix:** escape via `PDO::quote()` for the value literals, or double the quote char consistently; validate enum members against an allowlist charset.

---

## MEDIUM-2 — Hardcoded database password committed to the repository

**File:** `phpunit.xml:23`

```xml
<env name="MYSQL_PASSWORD" value="gEWqLsBP4BTkLRpb"/>
```

A real-looking credential is committed in the repository (and thus in git history). Even if this is only a local/test database, committed secrets get reused, and the value persists in history after any later edit. The CI workflow provisions its own throwaway MySQL, so this value is not needed for CI.

**Fix:** replace with a placeholder or read from an environment variable (`<env name="MYSQL_PASSWORD" value=""/>` overridden by the shell / `phpunit.xml.dist` + gitignored `phpunit.xml`). Rotate the password if the database it points at is reachable by anyone else. Purge from history if it is a shared secret.

---

## MEDIUM-3 — Migration discovery loads and instantiates arbitrary classes

**File:** `src/Migration/MigrationClassProvider.php:22-32`

The provider `require_once`'s every `*.php` file found under the migrations path and records *every* class it finds — the `is_subclass_of($class, Migration::class)` guard is **commented out**. `MigrationManager::runAllMigrations()` then instantiates each discovered class with `new $class($databaseProvider)` and calls `configure()/up()`.

**Consequences:**
- Any `.php` file dropped into (or path-traversed into) the migrations directory is executed on `migrate()`. If the migrations path is ever attacker-writable (shared deploy dir, upload folder, world-writable temp), this is remote code execution.
- Instantiating a non-`Migration` class with a single `DatabaseProviderInterface` arg, then calling `configure()`/`up()`, will fatal or behave unpredictably.

**Fix:** re-enable the `is_subclass_of` filter (and skip abstract classes). Constrain discovery to the intended directory and validate the resolved real path stays within it.

---

## LOW-1 — Migration failures are swallowed when a logger is present

**File:** `src/Migration/MigrationManager.php:44-59`

```php
} catch (\Throwable $e) {
    if ($this->logger === null) {
        throw $e;
    }
    $this->logger->error(...);   // swallowed — loop continues
}
```

If a logger is configured, a failed migration is logged but **not** rethrown, and `runAllMigrations()` proceeds to the next migration. The failed migration is (correctly) not recorded as finished, but subsequent migrations run against a half-migrated schema, and the overall `migrate()` call returns success. This risks silent schema drift and data corruption — a reliability/integrity issue rather than a direct vulnerability.

Additionally there is **no transaction** around each migration (`up()` may execute several statements via `TableBuilder`), so a mid-migration failure leaves a partially-applied migration with no rollback. Note MySQL auto-commits DDL regardless, so DDL migrations can't be made atomic there — but multi-statement DML migrations and PostgreSQL DDL can and should be wrapped.

**Fix:** rethrow after logging (or make continue-on-error opt-in and abort by default); wrap each migration in a transaction where the driver supports transactional DDL.

---

## LOW-2 — `ColumnType` enum parsing mishandles quotes/commas in members

**File:** `src/Utils/ColumnType.php` (`parseColumnType`)

Enum members are parsed with `explode(',', ...)` then `trim($value, '\'"')`. Enum values that legitimately contain a comma or an escaped quote are split/trimmed incorrectly, corrupting the round-tripped schema (and feeding the unescaped sinks in MEDIUM-1 / HIGH-3). Low impact but compounds the escaping issues above.

**Fix:** parse enum member lists with a quote-aware tokenizer rather than `explode`.

---

## Summary table

| ID | Severity | Issue | Location |
|----|----------|-------|----------|
| HIGH-1 | High | SQL injection in `insert()` values (no escaping, `exec()`) | `StringUtils::toSql`, `MySqlInsert`/`PgsqlInsert` |
| HIGH-2 | High | `EscapeUtils::escape` doesn't escape the quote char (identifier injection) | `EscapeUtils.php` |
| HIGH-3 | High | Code injection into generated migration files | `StringUtils::toCode`, `MigrationGenerator` |
| MEDIUM-1 | Medium | Unescaped DEFAULT / ENUM literals in column DDL | `MySqlChangeColumn`, `PgsqlChangeColumn` |
| MEDIUM-2 | Medium | Hardcoded DB password committed | `phpunit.xml` |
| MEDIUM-3 | Medium | Loads/instantiates arbitrary classes; subclass guard disabled | `MigrationClassProvider` |
| LOW-1 | Low | Migration failures swallowed with logger; no transactions | `MigrationManager` |
| LOW-2 | Low | Enum parsing mishandles quotes/commas | `ColumnType` |

## Top recommendations

1. **Stop hand-quoting values.** Bind `insert()` data with prepared statements (HIGH-1); the correct pattern already exists in `MigrationRepository::insertMigration`.
2. **Make `EscapeUtils::escape` actually escape** by doubling the quote character (HIGH-2) — a one-line fix that hardens every query builder at once.
3. **Generate code via `nette/php-generator`'s dumper**, not string concatenation (HIGH-3).
4. **Escape DEFAULT/ENUM literals** (MEDIUM-1) and re-enable the `is_subclass_of` migration filter (MEDIUM-3).
5. **Remove the committed password** and rethrow migration failures by default.
