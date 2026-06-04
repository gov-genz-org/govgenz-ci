<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSectorMediaId extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('sectors')) {
            return;
        }

        if ($this->db->fieldExists('media_id', 'sectors')) {
            return;
        }

        $this->forge->addColumn('sectors', [
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
        if (! $this->db->tableExists('sectors')) {
            return;
        }

        if ($this->db->fieldExists('media_alt', 'sectors')) {
            $this->forge->dropColumn('sectors', 'media_alt');
        }
        if ($this->db->fieldExists('media_id', 'sectors')) {
            $this->forge->dropColumn('sectors', 'media_id');
        }
    }
}
