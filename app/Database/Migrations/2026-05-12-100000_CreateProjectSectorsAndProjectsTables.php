<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectSectorsAndProjectsTables extends Migration
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
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'excerpt' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'body' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'project_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'candidat',
            ],
            'publication_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'draft',
            ],
            'sectors_csv' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'volunteers_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'budget_display' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'geography' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'launched_at' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'duration_months' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'progress_percent' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('project_status');
        $this->forge->addKey('publication_state');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey('published_at');
        $this->forge->createTable('project_projects');
    }

    public function down(): void
    {
        $this->forge->dropTable('project_projects', true);
    }
}
