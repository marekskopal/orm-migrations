<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Generator;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultTable;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use MarekSkopal\ORM\Migrations\Schema\ColumnSchema;
use MarekSkopal\ORM\Migrations\Schema\ForeignKeySchema;
use MarekSkopal\ORM\Migrations\Schema\IndexSchema;
use MarekSkopal\ORM\Migrations\Utils\StringUtils;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

readonly class MigrationGenerator
{
    public function __construct(private string $path)
    {
    }

    public function generate(CompareResult $compareResult, string $name = 'Migration', string $namespace = 'Migrations'): string
    {
        $phpFile = new PhpFile();
        $phpFile->setStrictTypes();

        $namespace = $phpFile->addNamespace($namespace);
        $namespace->addUse('MarekSkopal\ORM\Enum\Type');
        $namespace->addUse('MarekSkopal\ORM\Migrations\Migration\Migration');

        $class = $namespace->addClass($name);
        $class->setFinal();
        $class->setExtends(Migration::class);

        $this->generateUpMethod($compareResult, $class);
        $this->generateDownMethod($compareResult, $class);

        $printer = new PsrPrinter();
        $fileContent = $printer->printFile($phpFile);

        $fileName = date('Ymd_His_') . $name . '.php';

        file_put_contents(rtrim($this->path, '/') . '/' . $fileName, $fileContent);

        return $fileName;
    }

    private function generateUpMethod(CompareResult $compareResult, ClassType $class): void
    {
        $method = $class->addMethod('up');
        $method->setReturnType('void');

        foreach ($compareResult->tablesToCreate as $table) {
            $this->tableToMethodBody($table, $method);

            foreach ($table->columnsToCreate as $column) {
                $this->addColumnToMethodBody($column->changedColumn, $method);
            }

            foreach ($table->indexesToCreate as $index) {
                $this->addIndexToMethodBody($index->changedIndex, $method);
            }

            foreach ($table->foreignKeysToCreate as $foreignKey) {
                $this->addForeignKeyToMethodBody($foreignKey->changedForeignKey, $method);
            }

            $method->addBody("    ->create();\n");
        }

        foreach ($compareResult->tablesToDrop as $table) {
            $this->dropTableToMethodBody($table, $method);
        }

        foreach ($compareResult->tablesToAlter as $table) {
            $this->tableToMethodBody($table, $method);

            foreach ($table->columnsToCreate as $column) {
                $this->addColumnToMethodBody($column->changedColumn, $method);
            }

            foreach ($table->columnsToDrop as $column) {
                $this->dropColumnToMethodBody($column->changedColumn, $method);
            }

            foreach ($table->columnsToAlter as $column) {
                $this->alterColumnToMethodBody($column->changedColumn, $method);
            }

            $method->addBody('    ->alter();');
        }
    }

    private function generateDownMethod(CompareResult $compareResult, ClassType $class): void
    {
        $method = $class->addMethod('down');
        $method->setReturnType('void');

        foreach ($compareResult->tablesToAlter as $table) {
            $this->tableToMethodBody($table, $method);

            foreach (array_reverse($table->columnsToAlter) as $column) {
                if ($column->originalColumn === null) {
                    throw new \RuntimeException('Original column is required for alter column');
                }

                $this->alterColumnToMethodBody($column->originalColumn, $method);
            }

            foreach (array_reverse($table->columnsToDrop) as $column) {
                if ($column->originalColumn === null) {
                    throw new \RuntimeException('Original column is required for drop column');
                }

                $this->addColumnToMethodBody($column->originalColumn, $method);
            }

            foreach (array_reverse($table->columnsToCreate) as $column) {
                $this->dropColumnToMethodBody($column->changedColumn, $method);
            }

            $method->addBody('    ->alter();');
        }

        foreach (array_reverse($compareResult->tablesToDrop) as $table) {
            $this->tableToMethodBody($table, $method);

            foreach ($table->columnsToDrop as $column) {
                if ($column->originalColumn === null) {
                    throw new \RuntimeException('Original column is required for drop column');
                }

                $this->addColumnToMethodBody($column->originalColumn, $method);
            }

            $method->addBody('    ->create();');
        }

        foreach (array_reverse($compareResult->tablesToCreate) as $table) {
            $this->dropTableToMethodBody($table, $method);
        }
    }

    private function tableToMethodBody(CompareResultTable $table, Method $method): void
    {
        $method->addBody(sprintf('$this->table(%s)', StringUtils::toCode($table->name)));
    }

    private function dropTableToMethodBody(CompareResultTable $table, Method $method): void
    {
        $this->tableToMethodBody($table, $method);
        $method->addBody('    ->drop();');
    }

    private function changeColumnToMethodBody(string $change, ColumnSchema $column, Method $method): void
    {
        $code = sprintf(
            '    ->%sColumn(%s, %s',
            $change,
            StringUtils::toCode($column->name),
            'Type::' . $column->type->name,
        );

        if ($column->nullable) {
            $code .= ', nullable: ' . StringUtils::toCode($column->nullable);
        }

        if ($column->autoincrement) {
            $code .= ', autoincrement: ' . StringUtils::toCode($column->autoincrement);
        }

        if ($column->primary) {
            $code .= ', primary: ' . StringUtils::toCode($column->primary);
        }

        if ($column->size !== null) {
            $code .= ', size: ' . StringUtils::toCode($column->size);
        }

        if ($column->precision !== null) {
            $code .= ', size: ' . StringUtils::toCode($column->precision);
        }

        if ($column->scale !== null) {
            $code .= ', scale: ' . StringUtils::toCode($column->scale);
        }

        if ($column->enum !== null) {
            $code .= ', enum: ' . StringUtils::toCode($column->enum);
        }

        if ($column->default !== null) {
            $code .= ', default: ' . StringUtils::toCode($column->default);
        }

        $code .= ')';

        $method->addBody($code);
    }

    private function addColumnToMethodBody(ColumnSchema $column, Method $method): void
    {
        $this->changeColumnToMethodBody('add', $column, $method);
    }

    private function dropColumnToMethodBody(ColumnSchema $column, Method $method): void
    {
        $code = sprintf(
            '    ->dropColumn(%s)',
            StringUtils::toCode($column->name),
        );

        $method->addBody($code);
    }

    private function alterColumnToMethodBody(ColumnSchema $column, Method $method): void
    {
        $this->changeColumnToMethodBody('alter', $column, $method);
    }

    private function addIndexToMethodBody(IndexSchema $index, Method $method): void
    {
        $code = sprintf(
            '    ->addIndex(%s, %s, %s',
            StringUtils::toCode($index->columns),
            StringUtils::toCode($index->name),
            StringUtils::toCode($index->unique),
        );

        $code .= ')';

        $method->addBody($code);
    }

    private function addForeignKeyToMethodBody(ForeignKeySchema $foreignKey, Method $method): void
    {
        $code = sprintf(
            '    ->addForeignKey(%s, %s, %s, %s',
            StringUtils::toCode($foreignKey->column),
            StringUtils::toCode($foreignKey->referenceTable),
            StringUtils::toCode($foreignKey->referenceColumn),
            StringUtils::toCode($foreignKey->name),
        );

        $code .= ')';

        $method->addBody($code);
    }
}
