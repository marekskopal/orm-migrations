<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Generator;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultForeignKey;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultIndex;
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
#[UsesClass(CompareResultIndex::class)]
#[UsesClass(CompareResultForeignKey::class)]
#[UsesClass(StringUtils::class)]
final class MigrationGeneratorTest extends TestCase
{
    public function testGenerateCreateTable(): void
    {
        $migrationsPath = __DIR__ . '/Migrations';
        $migrationGenerator = new MigrationGenerator($migrationsPath . '/Generated');

        $compareResult = new CompareResult([
            new CompareResultTable(
                name: 'table_a',
                columnsToCreate: [
                    new CompareResultColumn('id', Type::Int, false, true, true, null, null, null, null),
                ],
                columnsToDrop: [],
                columnsToAlter: [],
                indexesToCreate: [],
                indexesToDrop: [],
                foreignKeysToCreate: [],
                foreignKeysToDrop: [],
            ),
            new CompareResultTable(
                name: 'table_b',
                columnsToCreate: [
                    new CompareResultColumn('id', Type::Int, false, true, true, null, null, null, null),
                    new CompareResultColumn('table_a_id', Type::Int, false, true, false, null, null, null, null),
                ],
                columnsToDrop: [],
                columnsToAlter: [],
                indexesToCreate: [
                    new CompareResultIndex(['table_a_id'], 'table_b_table_a_id_index', false),
                ],
                indexesToDrop: [],
                foreignKeysToCreate: [
                    new CompareResultForeignKey('table_a_id', 'table_a', 'id', 'table_b_table_a_id_fk'),
                ],
                foreignKeysToDrop: [],
            ),
        ], [], []);

        $migrationGenerator->generate($compareResult, 'CreateTableMigration', 'MarekSkopal\ORM\Migrations\Tests\Generator\Migrations');

        self::assertFileExists($migrationsPath . '/CreateTableMigration.php');

        $migrationContent = file_get_contents($migrationsPath . '/Generated/CreateTableMigration.php');
        $expectedContent = file_get_contents($migrationsPath . '/CreateTableMigration.php');
        self::assertSame($migrationContent, $expectedContent);
    }
}
