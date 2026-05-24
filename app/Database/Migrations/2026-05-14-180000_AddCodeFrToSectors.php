<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Libellé court FR pour filtres / pastilles (ex. Education, Digital) — le champ technique `code` reste inchangé pour sectors_csv.
 */
class AddCodeFrToSectors extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('sectors')) {
            return;
        }

        if (! $this->db->fieldExists('code_fr', 'sectors')) {
            $this->forge->addColumn('sectors', [
                'code_fr' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 48,
                    'null'       => true,
                    'after'      => 'code',
                ],
            ]);
        }

        $map = [
            'legal'          => 'Legal',
            'economy'        => 'Economy',
            'food'           => 'Food',
            'energy'         => 'Energy',
            'water'          => 'Water',
            'education'      => 'Education',
            'health'         => 'Health',
            'infrastructure' => 'Infrastructure',
            'digital'        => 'Digital',
            'territories'    => 'Territories',
            'environment'    => 'Environment',
            'mines'          => 'Mines',
            'security'       => 'Security',
            'citizen'        => 'Citizen',
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($map as $code => $label) {
            $this->db->table('sectors')
                ->where('code', $code)
                ->update(['code_fr' => $label, 'updated_at' => $now]);
        }

        $remaining = [];
        foreach ($this->db->table('sectors')->get()->getResultArray() as $row) {
            if (trim((string) ($row['code_fr'] ?? '')) !== '') {
                continue;
            }
            $remaining[] = $row;
        }

        foreach ($remaining as $row) {
            $code = strtolower(trim((string) ($row['code'] ?? '')));
            if ($code === '') {
                continue;
            }
            $fallback = strtoupper($code[0]) . substr($code, 1);
            $this->db->table('sectors')->where('id', (int) ($row['id'] ?? 0))->update([
                'code_fr'    => $fallback,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('sectors')) {
            return;
        }

        if ($this->db->fieldExists('code_fr', 'sectors')) {
            $this->forge->dropColumn('sectors', 'code_fr');
        }
    }
}
