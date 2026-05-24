<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectContributionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'project_slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'project_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'default'    => 'fr',
            ],
            'contribution_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
            ],
            'donor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'amount' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'items' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'quantity' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'available_from' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'pickup_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'can_deliver' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'default'    => 'new',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['project_slug', 'locale']);
        $this->forge->createTable('project_contributions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('project_contributions', true);
    }
}
