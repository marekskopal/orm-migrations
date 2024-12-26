<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Tests\Generator\Migrations;

use MarekSkopal\ORM\Migrations\Migration\Migration;

final class CreateTableMigration extends Migration
{
    public function up(): void
    {
        $this->table('table_a')
            ->addColumn('id', 'int', autoincrement: true, primary: true)
            ->create();

        $this->table('table_b')
            ->addColumn('id', 'int', autoincrement: true, primary: true)
            ->addColumn('table_a_id', 'int', autoincrement: true)
            ->addIndex(['table_a_id'], 'table_b_table_a_id_index', false)
            ->addForeignKey('table_a_id', 'table_a', 'id', 'table_b_table_a_id_fk')
            ->create();
    }

    public function down(): void
    {
        $this->table('table_b')->drop();
        $this->table('table_a')->drop();
    }
}
