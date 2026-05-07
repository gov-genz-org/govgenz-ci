<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sécurise les environnements où la migration 223000 n’a pas été jouée (colonnes manquantes).
 */
class EnsureCmsPagesStructuredBodyColumns extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        if (! $this->db->fieldExists('content_mode', 'cms_pages')) {
            $this->forge->addColumn('cms_pages', [
                'content_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 16,
                    'default'    => 'html',
                ],
            ]);
        }

        if (! $this->db->fieldExists('body_blocks', 'cms_pages')) {
            $this->forge->addColumn('cms_pages', [
                'body_blocks' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        if ($this->db->fieldExists('body_blocks', 'cms_pages')) {
            $this->forge->dropColumn('cms_pages', 'body_blocks');
        }

        if ($this->db->fieldExists('content_mode', 'cms_pages')) {
            $this->forge->dropColumn('cms_pages', 'content_mode');
        }
    }
}
