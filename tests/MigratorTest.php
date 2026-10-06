<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests;

use MarekSkopal\ORM\Database\MySqlDatabase;
use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProvider;
use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderFactory;
use MarekSkopal\ORM\Migrations\Migration\MigrationClassProvider;
use MarekSkopal\ORM\Migrations\Migration\MigrationManager;
use MarekSkopal\ORM\Migrations\Migration\MigrationRepository;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlQueryFactory;
use MarekSkopal\ORM\Migrations\Migrator;
use MarekSkopal\ORM\Migrations\Schema\Converter\Type\MySqlTypeConverter;
use MarekSkopal\ORM\Migrations\Schema\Provider\MySqlSchemaProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Migrator::class)]
#[UsesClass(DatabaseProvider::class)]
#[UsesClass(DatabaseProviderFactory::class)]
#[UsesClass(MigrationClassProvider::class)]
#[UsesClass(MigrationManager::class)]
#[UsesClass(MigrationRepository::class)]
#[UsesClass(MySqlQueryFactory::class)]
#[UsesClass(MySqlTypeConverter::class)]
#[UsesClass(MySqlSchemaProvider::class)]
final class MigratorTest extends TestCase
{
    public function testMigrateClearsTheStatementCacheOfTheConnection(): void
    {
        $database = $this->createDatabase();
        $directory = sys_get_temp_dir() . '/orm-migrations-' . bin2hex(random_bytes(6));
        mkdir($directory);

        try {
            new Migrator($directory, $database)->migrate();
        } finally {
            rmdir($directory);
        }

        // Statements the ORM cached before the migrations may refer to changed tables.
        self::assertSame(1, $database->statementCacheClears);
    }

    /** @return MySqlDatabase&object{statementCacheClears: int} */
    private function createDatabase(): MySqlDatabase
    {
        return new class (
            host: (string) getenv('MYSQL_HOST'),
            username: (string) getenv('MYSQL_USER'),
            password: (string) getenv('MYSQL_PASSWORD'),
            database: (string) getenv('MYSQL_DATABASE'),
            port: getenv('MYSQL_PORT') !== false ? (int) getenv('MYSQL_PORT') : 3306,
        ) extends MySqlDatabase {
            public int $statementCacheClears = 0;

            public function clearStatementCache(): void
            {
                $this->statementCacheClears++;

                parent::clearStatementCache();
            }
        };
    }
}
