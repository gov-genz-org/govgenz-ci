<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStructureUnitsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
            ],
            'title_fr' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'title_en' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description_fr' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'description_en' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'contact_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey(['is_active', 'sort_order']);
        $this->forge->createTable('structure_units', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('structure_units', true);
    }
}
