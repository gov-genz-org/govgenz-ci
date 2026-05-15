<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectBudgetStructured extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('project_projects', [
            'budget_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,4',
                'null'       => true,
            ],
            'budget_scale' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
            ],
            'budget_ariary' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        helper('project');

        $rows = $this->db->table('project_projects')->get()->getResultArray();
        foreach ($rows as $row) {
            $parts = project_budget_infer_parts_from_legacy((string) ($row['budget_display'] ?? ''));
            if ($parts === null) {
                continue;
            }
            $this->db->table('project_projects')->where('id', (int) $row['id'])->update([
                'budget_amount' => $parts['amount'],
                'budget_scale'  => $parts['scale'],
                'budget_ariary' => $parts['ariary'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('project_projects', ['budget_amount', 'budget_scale', 'budget_ariary']);
    }
}
