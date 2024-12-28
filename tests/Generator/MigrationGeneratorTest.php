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
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\ColumnSchemaFixture;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\CompareResultColumnFixture;
use MarekSkopal\ORM\Migrations\Tests\Fixtures\TestEnum;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use UnitEnum;

#[CoversClass(MigrationGenerator::class)]
#[UsesClass(CompareResult::class)]
#[UsesClass(CompareResultTable::class)]
#[UsesClass(CompareResultColumn::class)]
#[UsesClass(CompareResultIndex::class)]
#[UsesClass(CompareResultForeignKey::class)]
#[UsesClass(ColumnSchema::class)]
#[UsesClass(ForeignKeySchema::class)]
#[UsesClass(IndexSchema::class)]
#[UsesClass(StringUtils::class)]
final class MigrationGeneratorTest extends TestCase
{
    private const string MigrationsPath = __DIR__ . '/Migrations';
    private const string MigrationsGeneratedPath = __DIR__ . '/Migrations/Generated';

    public function testGenerateCreateTable(): void
    {
        $migrationGenerator = new MigrationGenerator(self::MigrationsGeneratedPath);

        $compareResult = new CompareResult([
            new CompareResultTable(
                name: 'table_a',
                columnsToCreate: [
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'id',
                            type: Type::Int,
                            primary: true,
                            autoincrement: true,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'name',
                            type: Type::String,
                            nullable: true,
                            size: 255,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'address',
                            type: Type::String,
                            size: 50,
                            default: 'New York',
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'score',
                            type: Type::Int,
                            size: 10,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'price',
                            type: Type::Decimal,
                            precision: 10,
                            scale: 2,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'type',
                            type: Type::Enum,
                            enum: array_map(fn(UnitEnum $item) => $item->value, TestEnum::cases()),
                            default: TestEnum::A,
                        ),
                    ),
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
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'id',
                            type: Type::Int,
                            primary: true,
                            autoincrement: true,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'table_a_id',
                            type: Type::Int,
                        ),
                    ),
                ],
                columnsToDrop: [],
                columnsToAlter: [],
                indexesToCreate: [
                    CompareResultIndex::fromIndexSchema(new IndexSchema(['table_a_id'], 'table_b_table_a_id_index', false)),
                ],
                indexesToDrop: [],
                foreignKeysToCreate: [
                    CompareResultForeignKey::fromForeignKeySchema(
                        new ForeignKeySchema('table_a_id', 'table_a', 'id', 'table_b_table_a_id_fk'),
                    ),
                ],
                foreignKeysToDrop: [],
            ),
        ], [], []);

        $fileName = $migrationGenerator->generate(
            $compareResult,
            'CreateTableMigration',
            'MarekSkopal\ORM\Migrations\Tests\Generator\Migrations',
        );

        self::assertFileExists(self::MigrationsGeneratedPath . '/' . $fileName);

        $migrationContent = file_get_contents(self::MigrationsGeneratedPath . '/' . $fileName);
        $expectedContent = file_get_contents(self::MigrationsPath . '/CreateTableMigration.php');
        self::assertSame($migrationContent, $expectedContent);
    }

    public function testGenerateDropTable(): void
    {
        $migrationGenerator = new MigrationGenerator(self::MigrationsGeneratedPath);

        $compareResult = new CompareResult([], [
            new CompareResultTable(
                name: 'table_a',
                columnsToCreate: [],
                columnsToDrop: [
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'id',
                            type: Type::Int,
                            primary: true,
                            autoincrement: true,
                        ),
                        ColumnSchemaFixture::create(
                            name: 'id',
                            type: Type::Int,
                            primary: true,
                            autoincrement: true,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'name',
                            type: Type::String,
                            nullable: true,
                            size: 255,
                        ),
                        ColumnSchemaFixture::create(
                            name: 'name',
                            type: Type::String,
                            nullable: true,
                            size: 255,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'address',
                            type: Type::String,
                            size: 50,
                            default: 'New York',
                        ),
                        ColumnSchemaFixture::create(
                            name: 'address',
                            type: Type::String,
                            size: 50,
                            default: 'New York',
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'score',
                            type: Type::Int,
                            size: 10,
                        ),
                        ColumnSchemaFixture::create(
                            name: 'score',
                            type: Type::Int,
                            size: 10,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'price',
                            type: Type::Decimal,
                            precision: 10,
                            scale: 2,
                        ),
                        ColumnSchemaFixture::create(
                            name: 'price',
                            type: Type::Decimal,
                            precision: 10,
                            scale: 2,
                        ),
                    ),
                    CompareResultColumnFixture::create(
                        ColumnSchemaFixture::create(
                            name: 'type',
                            type: Type::Enum,
                            enum: array_map(fn(UnitEnum $item) => $item->value, TestEnum::cases()),
                            default: TestEnum::A,
                        ),
                        ColumnSchemaFixture::create(
                            name: 'type',
                            type: Type::Enum,
                            enum: array_map(fn(UnitEnum $item) => $item->value, TestEnum::cases()),
                            default: TestEnum::A,
                        ),
                    ),
                ],
                columnsToAlter: [],
                indexesToCreate: [],
                indexesToDrop: [],
                foreignKeysToCreate: [],
                foreignKeysToDrop: [],
            ),
        ], []);

        $fileName = $migrationGenerator->generate(
            $compareResult,
            'DropTableMigration',
            'MarekSkopal\ORM\Migrations\Tests\Generator\Migrations',
        );

        self::assertFileExists(self::MigrationsGeneratedPath . '/' . $fileName);

        $migrationContent = file_get_contents(self::MigrationsGeneratedPath . '/' . $fileName);
        $expectedContent = file_get_contents(self::MigrationsPath . '/DropTableMigration.php');
        self::assertSame($migrationContent, $expectedContent);
    }

    protected function tearDown(): void
    {
        $files = scandir(self::MigrationsGeneratedPath);
        if ($files === false) {
            return;
        }
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') {
                continue;
            }

            unlink(self::MigrationsGeneratedPath . '/' . $file);
        }
    }
}
