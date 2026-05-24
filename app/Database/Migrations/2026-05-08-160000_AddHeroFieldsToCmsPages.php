<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHeroFieldsToCmsPages extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        if (! $this->db->fieldExists('hero_overline', 'cms_pages')) {
            $this->forge->addColumn('cms_pages', [
                'hero_overline' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('hero_title', 'cms_pages')) {
            $this->forge->addColumn('cms_pages', [
                'hero_title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('hero_lead', 'cms_pages')) {
            $this->forge->addColumn('cms_pages', [
                'hero_lead' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('hero_image_id', 'cms_pages')) {
            $this->forge->addColumn('cms_pages', [
                'hero_image_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('hero_image_alt', 'cms_pages')) {
            $this->forge->addColumn('cms_pages', [
                'hero_image_alt' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        foreach (['hero_image_alt', 'hero_image_id', 'hero_lead', 'hero_title', 'hero_overline'] as $col) {
            if ($this->db->fieldExists($col, 'cms_pages')) {
                $this->forge->dropColumn('cms_pages', $col);
            }
        }
    }
}
