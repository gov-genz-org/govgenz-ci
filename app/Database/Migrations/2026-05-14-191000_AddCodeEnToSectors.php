<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Libellé court EN pour pastilles filtres /projects (locale EN) — le code technique reste inchangé.
 */
class AddCodeEnToSectors extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('sectors')) {
            return;
        }

        if (! $this->db->fieldExists('code_en', 'sectors')) {
            $this->forge->addColumn('sectors', [
                'code_en' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 48,
                    'null'       => true,
                    'after'      => 'code_fr',
                ],
            ]);
        }

        $now = date('Y-m-d H:i:s');
        foreach ($this->db->table('sectors')->get()->getResultArray() as $row) {
            $cf = trim((string) ($row['code_fr'] ?? ''));
            if ($cf === '') {
                continue;
            }
            if (trim((string) ($row['code_en'] ?? '')) !== '') {
                continue;
            }
            $this->db->table('sectors')->where('id', (int) ($row['id'] ?? 0))->update([
                'code_en'    => $cf,
                'updated_at' => $now,
            ]);
        }

        foreach ($this->db->table('sectors')->get()->getResultArray() as $row) {
            if (trim((string) ($row['code_en'] ?? '')) !== '') {
                continue;
            }
            $code = strtolower(trim((string) ($row['code'] ?? '')));
            if ($code === '') {
                continue;
            }
            $fallback = strtoupper($code[0]) . substr($code, 1);
            $this->db->table('sectors')->where('id', (int) ($row['id'] ?? 0))->update([
                'code_en'    => $fallback,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('sectors')) {
            return;
        }

        if ($this->db->fieldExists('code_en', 'sectors')) {
            $this->forge->dropColumn('sectors', 'code_en');
        }
    }
}
