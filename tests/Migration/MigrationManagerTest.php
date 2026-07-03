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
        file_put_contents(
            self::Path . '/NotAMigration3.php',
            "<?php\n\ndeclare(strict_types=1);\n\n"
            . "namespace MarekSkopal\\ORM\\Migrations\\Tests\\Generator\\Migrations;\n\n"
            . "final class NotAMigration3\n{\n}\n",
        );
    }

    public function testRunAllMigrations(): void
    {
        $pdo = self::createStub(PDO::class);
        $pdo->method('exec')->willReturn(1);
        $database = self::createStub(DatabaseInterface::class);
        $database->method('getPdo')->willReturn($pdo);
        $databaseProvider = self::createStub(DatabaseProviderInterface::class);
        $databaseProvider->method('getDatabase')->willReturn($database);
        $logger = self::createStub(LoggerInterface::class);
        $migrationRepository = $this->createMock(MigrationRepository::class);
        $migrationRepository->expects($this->once())->method('createMigrationTable');
        $migrationRepository->expects($this->once())->method('getFinishedMigrations')->willReturn([]);
        // Only the two Migration subclasses run; NotAMigration3 is skipped.
        $migrationRepository->expects($this->exactly(2))->method('insertMigration');

        $migrationManager = new MigrationManager($databaseProvider, $migrationRepository, self::Path, $logger);

        $migrationManager->runAllMigrations();
    }

    public function testFailingMigrationIsRethrownEvenWithLogger(): void
    {
        $databaseProvider = self::createStub(DatabaseProviderInterface::class);
        $databaseProvider->method('getQueryFactory')->willThrowException(new \RuntimeException('migration failed'));
        $logger = self::createStub(LoggerInterface::class);
        $migrationRepository = $this->createMock(MigrationRepository::class);
        $migrationRepository->method('getFinishedMigrations')->willReturn([]);
        // A failed migration must not be recorded as finished.
        $migrationRepository->expects($this->never())->method('insertMigration');

        $migrationManager = new MigrationManager($databaseProvider, $migrationRepository, self::Path, $logger);

        $this->expectException(\RuntimeException::class);
        $migrationManager->runAllMigrations();
    }

    protected function tearDown(): void
    {
        unlink(self::Path . '/CreateTableMigration1.php');
        unlink(self::Path . '/CreateTableMigration2.php');
        unlink(self::Path . '/NotAMigration3.php');
    }
}
