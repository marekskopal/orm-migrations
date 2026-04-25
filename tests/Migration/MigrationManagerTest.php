<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration;

use MarekSkopal\ORM\Database\DatabaseInterface;
use MarekSkopal\ORM\Migrations\Database\Provider\DatabaseProviderInterface;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use MarekSkopal\ORM\Migrations\Migration\MigrationClass;
use MarekSkopal\ORM\Migrations\Migration\MigrationClassProvider;
use MarekSkopal\ORM\Migrations\Migration\MigrationManager;
use MarekSkopal\ORM\Migrations\Migration\MigrationRepository;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddColumn;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddForeignKey;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlAddIndex;
use MarekSkopal\ORM\Migrations\Migration\Query\Mysql\MySqlCreateTable;
use MarekSkopal\ORM\Migrations\Migration\TableBuilder;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(MigrationManager::class)]
#[UsesClass(Migration::class)]
#[UsesClass(MigrationClass::class)]
#[UsesClass(MigrationClassProvider::class)]
#[UsesClass(MySqlAddColumn::class)]
#[UsesClass(MySqlAddForeignKey::class)]
#[UsesClass(MySqlAddIndex::class)]
#[UsesClass(MySqlCreateTable::class)]
#[UsesClass(TableBuilder::class)]
final class MigrationManagerTest extends TestCase
{
    private const string Path = __DIR__ . '/../Generator/Migrations/Generated';

    protected function setUp(): void
    {
        if (!is_dir(self::Path)) {
            mkdir(self::Path);
        }

        $content = (string) file_get_contents(__DIR__ . '/../Generator/Migrations/CreateTableMigration.php');
        file_put_contents(
            self::Path . '/CreateTableMigration1.php',
            str_replace('class CreateTableMigration', 'class CreateTableMigration1', $content),
        );
        file_put_contents(
            self::Path . '/CreateTableMigration2.php',
            str_replace('class CreateTableMigration', 'class CreateTableMigration2', $content),
        );
    }

    public function testRunAllMigrations(): void
    {
        $pdo = $this->createStub(PDO::class);
        $pdo->method('exec')->willReturn(1);
        $database = $this->createStub(DatabaseInterface::class);
        $database->method('getPdo')->willReturn($pdo);
        $databaseProvider = $this->createStub(DatabaseProviderInterface::class);
        $databaseProvider->method('getDatabase')->willReturn($database);
        $logger = $this->createStub(LoggerInterface::class);
        $migrationRepository = $this->createMock(MigrationRepository::class);
        $migrationRepository->expects($this->once())->method('createMigrationTable');
        $migrationRepository->expects($this->once())->method('getFinishedMigrations')->willReturn([]);
        $migrationRepository->expects($this->exactly(2))->method('insertMigration');

        $migrationManager = new MigrationManager($databaseProvider, $migrationRepository, self::Path, $logger);

        $migrationManager->runAllMigrations();
    }

    protected function tearDown(): void
    {
        unlink(self::Path . '/CreateTableMigration1.php');
        unlink(self::Path . '/CreateTableMigration2.php');
    }
}
