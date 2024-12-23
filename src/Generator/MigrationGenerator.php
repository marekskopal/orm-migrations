<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Generator;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Compare\Result\CompareResultColumn;
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

    public function generate(CompareResult $compareResult, string $name = 'Migration', string $namespace = 'Migrations'): void
    {
        $phpFile = new PhpFile();
        $phpFile->setStrictTypes();

        $namespace = $phpFile->addNamespace($namespace);
        $namespace->addUse('MarekSkopal\ORM\Migrations\Migration\Migration');

        $class = $namespace->addClass($name);
        $class->setFinal();
        $class->setExtends(Migration::class);

        $this->generateUpMethod($compareResult, $class);
        $this->generateDownMethod($compareResult, $class);

        $printer = new PsrPrinter();
        $fileContent = $printer->printFile($phpFile);

        file_put_contents(rtrim($this->path, '/') . '/' . $name . '.php', $fileContent);
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

            $method->addBody('    ->create();');
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

        foreach ($compareResult->tablesToCreate as $table) {
            $method->addBody(sprintf('$this->table(\'%s\')->drop();', $table->name));
        }

        foreach ($compareResult->tablesToDrop as $table) {
            $method->addBody(sprintf('$this->table(\'%s\')', $table->name));

            foreach ($table->columnsToCreate as $column) {
                $method->addBody(sprintf('    ->addColumn(\'%s\', \'%s\')', $column->name, $column->type));
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
        $method->addBody(
            sprintf(
                '    ->addColumn(%s, %s, %s, %s, %s, %s)',
                StringUtils::toCode($column->name),
                StringUtils::toCode($column->type),
                StringUtils::toCode($column->nullable),
                StringUtils::toCode($column->autoincrement),
                StringUtils::toCode($column->primary),
                StringUtils::toCode($column->default),
            ),
        );
    }
}
