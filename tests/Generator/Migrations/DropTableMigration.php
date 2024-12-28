<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Generator\Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class DropTableMigration extends Migration
{
    public function up(): void
    {
        $this->table('table_a')
            ->drop();
    }

    public function down(): void
    {
        $this->table('table_a')
            ->addColumn('id', Type::Int, autoincrement: true, primary: true)
            ->addColumn('name', Type::String, nullable: true, size: 255)
            ->addColumn('address', Type::String, size: 50, default: 'New York')
            ->addColumn('score', Type::Int, size: 10)
            ->addColumn('price', Type::Decimal, precision: 10, scale: 2)
            ->addColumn('type', Type::Enum, enum: ['a', 'b', 'c'], default: 'a')
            ->create();
    }
}
