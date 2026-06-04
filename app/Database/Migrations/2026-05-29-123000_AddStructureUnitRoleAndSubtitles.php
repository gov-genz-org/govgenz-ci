<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStructureUnitRoleAndSubtitles extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('structure_units')) {
            return;
        }

        if (! $this->db->fieldExists('unit_role', 'structure_units')) {
            $this->forge->addColumn('structure_units', [
                'unit_role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 16,
                    'default'    => 'function',
                    'after'      => 'code',
                ],
            ]);
        }

        foreach (['subtitle_fr', 'subtitle_en'] as $field) {
            if (! $this->db->fieldExists($field, 'structure_units')) {
                $this->forge->addColumn('structure_units', [
                    $field => [
                        'type' => 'TEXT',
                        'null' => true,
                        'after' => 'title_en',
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('structure_units')) {
            return;
        }

        foreach (['subtitle_fr', 'subtitle_en', 'unit_role'] as $field) {
            if ($this->db->fieldExists($field, 'structure_units')) {
                $this->forge->dropColumn('structure_units', $field);
            }
        }
    }
}
