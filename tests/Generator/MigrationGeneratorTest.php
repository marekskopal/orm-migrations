<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Generator;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultTable;
use MarekSkopal\ORM\Migrations\Generator\MigrationGenerator;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MigrationGenerator::class)]
#[UsesClass(CompareResult::class)]
#[UsesClass(CompareResultTable::class)]
#[UsesClass(CompareResultColumn::class)]
#[UsesClass(StringUtils::class)]
class MigrationGeneratorTest extends TestCase
{
    public function testGenerateCreateTable(): void
    {
        $migrationsPath = __DIR__ . '/Migrations';
        $migrationGenerator = new MigrationGenerator($migrationsPath . '/Generated');

        $compareResult = new CompareResult([
            new CompareResultTable('table_a', [
                new CompareResultColumn('id', 'int', false, true, true, null, null, null, null),
            ], [], []),
        ], [], []);

        $migrationGenerator->generate($compareResult, 'CreateTableMigration', 'MarekSkopal\ORM\Migrations\Tests\Generator\Migrations');

        self::assertFileExists($migrationsPath . '/CreateTableMigration.php');

        $migrationContent = file_get_contents($migrationsPath . '/Generated/CreateTableMigration.php');
        $expectedContent = file_get_contents($migrationsPath . '/CreateTableMigration.php');
        self::assertSame($migrationContent, $expectedContent);
    }
}
