<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeclarationItemBodyBlocks extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        if (! $this->db->fieldExists('body', 'declaration_items')) {
            $this->forge->addColumn('declaration_items', [
                'body' => [
                    'type' => 'MEDIUMTEXT',
                    'null' => true,
                    'after' => 'summary',
                ],
            ]);
        }

        if (! $this->db->fieldExists('body_content_mode', 'declaration_items')) {
            $this->forge->addColumn('declaration_items', [
                'body_content_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 16,
                    'default'    => 'blocks',
                    'after'      => 'body',
                ],
            ]);
        }

        if (! $this->db->fieldExists('body_blocks', 'declaration_items')) {
            $this->forge->addColumn('declaration_items', [
                'body_blocks' => [
                    'type' => 'MEDIUMTEXT',
                    'null' => true,
                    'after' => 'body_content_mode',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        if ($this->db->fieldExists('body_blocks', 'declaration_items')) {
            $this->forge->dropColumn('declaration_items', 'body_blocks');
        }
        if ($this->db->fieldExists('body_content_mode', 'declaration_items')) {
            $this->forge->dropColumn('declaration_items', 'body_content_mode');
        }
        if ($this->db->fieldExists('body', 'declaration_items')) {
            $this->forge->dropColumn('declaration_items', 'body');
        }
    }
}
