<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Migration;

use MarekSkopal\ORM\Migrations\Migration\MigrationClass;
use MarekSkopal\ORM\Migrations\Migration\MigrationClassProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MigrationClassProvider::class)]
#[UsesClass(MigrationClass::class)]
final class MigrationClassProviderTest extends TestCase
{
    private const string Path = __DIR__ . '/../Generator/Migrations/Generated';

    protected function setUp(): void
    {
        if (!is_dir(self::Path)) {
            mkdir(self::Path);
        }

        copy(__DIR__ . '/../Generator/Migrations/CreateTableMigration.php', self::Path . '/CreateTableMigration1.php');
        copy(__DIR__ . '/../Generator/Migrations/CreateTableMigration.php', self::Path . '/CreateTableMigration2.php');
    }

    public function testGetMigrationClasses(): void
    {
        $migrationClassProvider = new MigrationClassProvider(self::Path);

        $migrationClasses = $migrationClassProvider->getMigrationClasses();
        self::assertCount(2, $migrationClasses);
        self::assertSame('MarekSkopal\ORM\Migrations\Tests\Generator\Migrations\CreateTableMigration', $migrationClasses[0]->class);
    }

    public function testGetMigrationClassesSortedByFilename(): void
    {
        $migrationClassProvider = new MigrationClassProvider(self::Path);

        $migrationClasses = $migrationClassProvider->getMigrationClasses();

        self::assertCount(2, $migrationClasses);
        self::assertStringEndsWith('CreateTableMigration1.php', $migrationClasses[0]->file);
        self::assertStringEndsWith('CreateTableMigration2.php', $migrationClasses[1]->file);
    }

    protected function tearDown(): void
    {
        unlink(self::Path . '/CreateTableMigration1.php');
        unlink(self::Path . '/CreateTableMigration2.php');
    }
}
