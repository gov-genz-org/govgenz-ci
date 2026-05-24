<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStructuredBodyToCmsPages extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $this->forge->addColumn('cms_pages', [
            'content_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'default'    => 'html',
            ],
            'body_blocks' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $this->forge->dropColumn('cms_pages', ['content_mode', 'body_blocks']);
    }
}
