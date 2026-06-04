<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStructureUnitMediaId extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('structure_units')) {
            return;
        }

        if ($this->db->fieldExists('media_id', 'structure_units')) {
            return;
        }

        $this->forge->addColumn('structure_units', [
            'media_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'contact_email',
            ],
            'media_alt' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'media_id',
            ],
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('structure_units')) {
            return;
        }

        if ($this->db->fieldExists('media_alt', 'structure_units')) {
            $this->forge->dropColumn('structure_units', 'media_alt');
        }
        if ($this->db->fieldExists('media_id', 'structure_units')) {
            $this->forge->dropColumn('structure_units', 'media_id');
        }
    }
}
