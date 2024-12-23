<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Generator\Migrations;

use MarekSkopal\ORM\Migrations\Migration\Migration;

final class CreateTableMigration extends Migration
{
    public function up(): void
    {
        $this->table('table_a')
            ->addColumn('id', 'int', false, true, true, null)
            ->create();
    }

    public function down(): void
    {
        $this->table('table_a')->drop();
    }
}
