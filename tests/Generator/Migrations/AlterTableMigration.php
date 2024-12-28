<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Generator\Migrations;

use MarekSkopal\ORM\Enum\Type;
use MarekSkopal\ORM\Migrations\Migration\Migration;

final class AlterTableMigration extends Migration
{
    public function up(): void
    {
        $this->table('table_a')
            ->addColumn('first_name', Type::String, nullable: true, size: 255)
            ->dropColumn('address')
            ->alterColumn('score', Type::Int, size: 20)
            ->alter();
    }

    public function down(): void
    {
        $this->table('table_a')
            ->alterColumn('score', Type::Int, size: 10)
            ->addColumn('address', Type::String, size: 50, default: 'New York')
            ->dropColumn('first_name')
            ->alter();
    }
}
