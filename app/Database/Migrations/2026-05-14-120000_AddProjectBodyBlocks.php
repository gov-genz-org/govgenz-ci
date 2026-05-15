<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectBodyBlocks extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('body_content_mode', 'project_projects')) {
            $this->forge->addColumn('project_projects', [
                'body_content_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 16,
                    'default'    => 'html',
                ],
            ]);
        }
        if (! $this->db->fieldExists('body_blocks', 'project_projects')) {
            $this->forge->addColumn('project_projects', [
                'body_blocks' => [
                    'type' => 'MEDIUMTEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('body_blocks', 'project_projects')) {
            $this->forge->dropColumn('project_projects', 'body_blocks');
        }
        if ($this->db->fieldExists('body_content_mode', 'project_projects')) {
            $this->forge->dropColumn('project_projects', 'body_content_mode');
        }
    }
}
