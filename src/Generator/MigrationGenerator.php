<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Generator;

use MarekSkopal\ORM\Migrations\Compare\Result\CompareResult;
use MarekSkopal\ORM\Migrations\Migration\Migration;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Printer;

readonly class MigrationGenerator
{
    public function __construct(private string $path)
    {
    }

    public function generate(CompareResult $compareResult, ?string $name = null): void
    {
        $name = $name ?? 'Migration';

        $class = new ClassType($name);
        $class->setFinal();
        $class->setExtends(Migration::class);

        $this->generateUpMethod($compareResult, $class);

        $printer = new Printer();
        file_put_contents(rtrim($this->path, '/') . '/' . $name . '.php', $printer->printClass($class));
    }

    private function generateUpMethod(CompareResult $compareResult, ClassType $class): void
    {
        $method = $class->addMethod('up');

        foreach ($compareResult->tablesToCreate as $table) {
            $method->addBody(sprintf('$this->table(\'%s\'', $table->name));

            foreach ($table->columnsToCreate as $column) {
                $method->addBody(sprintf('    ->addColumn(\'%s\', \'%s\');', $column->name, $column->type));
            }

            $method->addBody('->create();');
        }

        foreach ($compareResult->tablesToDrop as $table) {
            $method->addBody(sprintf('$this->table(\'%s\')->drop();', $table->name));
        }

        foreach ($compareResult->tablesToAlter as $table) {
            $method->addBody(sprintf('$this->table(\'%s\'', $table->name));

            foreach ($table->columnsToCreate as $column) {
                $method->addBody(sprintf('    ->addColumn(\'%s\', \'%s\');', $column->name, $column->type));
            }

            foreach ($table->columnsToDrop as $column) {
                $method->addBody(sprintf('    ->dropColumn(\'%s\');', $column->name));
            }

            $method->addBody('->execute();');
        }
    }
}
