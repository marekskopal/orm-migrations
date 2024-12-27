<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Generator;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultForeignKey;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultIndex;
use MarekSkopal\ORM\Migrations\Migration\Migration;
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
            $method->addBody(sprintf('$this->table(\'%s\')', $table->name));

            foreach ($table->columnsToCreate as $column) {
                $this->addColumnToMethodBody($column, $method);
            }

            foreach ($table->indexesToCreate as $index) {
                $this->addIndexToMethodBody($index, $method);
            }

            foreach ($table->foreignKeysToCreate as $foreignKey) {
                $this->addForeignKeyToMethodBody($foreignKey, $method);
            }

            $method->addBody("    ->create();\n");
        }

        foreach ($compareResult->tablesToDrop as $table) {
            $method->addBody(sprintf('$this->table(%s)->drop();', StringUtils::toCode($table->name)));
        }

        foreach ($compareResult->tablesToAlter as $table) {
            $method->addBody(sprintf('$this->table(%s', StringUtils::toCode($table->name)));

            foreach ($table->columnsToCreate as $column) {
                $this->addColumnToMethodBody($column, $method);
            }

            foreach ($table->columnsToDrop as $column) {
                $method->addBody(sprintf('    ->dropColumn(%s);', StringUtils::toCode($column->name)));
            }

            $method->addBody('->execute();');
        }
    }

    private function generateDownMethod(CompareResult $compareResult, ClassType $class): void
    {
        $method = $class->addMethod('down');
        $method->setReturnType('void');

        foreach (array_reverse($compareResult->tablesToCreate) as $table) {
            $method->addBody(sprintf('$this->table(\'%s\')->drop();', $table->name));
        }

        foreach ($compareResult->tablesToDrop as $table) {
            $method->addBody(sprintf('$this->table(\'%s\')', $table->name));

            foreach ($table->columnsToCreate as $column) {
                $this->addColumnToMethodBody($column, $method);
            }

            $method->addBody('    ->create();');
        }

        foreach ($compareResult->tablesToAlter as $table) {
            $method->addBody(sprintf('$this->table(\'%s\'', $table->name));

            foreach ($table->columnsToCreate as $column) {
                $method->addBody(sprintf('    ->dropColumn(\'%s\');', $column->name));
            }

            foreach ($table->columnsToDrop as $column) {
                $this->addColumnToMethodBody($column, $method);
            }

            $method->addBody('->execute();');
        }
    }

    private function addColumnToMethodBody(CompareResultColumn $column, Method $method): void
    {
        $code = sprintf(
            '    ->addColumn(%s, %s',
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

    private function addIndexToMethodBody(CompareResultIndex $index, Method $method): void
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

    private function addForeignKeyToMethodBody(CompareResultForeignKey $foreignKey, Method $method): void
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
