<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectExchangeRates extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'label_year' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => '2026',
            ],
            'usd_ariary' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 4500,
            ],
            'eur_ariary' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 4900,
            ],
            'cny_ariary' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 620,
            ],
            'jpy_ariary' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 30,
            ],
            'fcfa_ariary' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,4',
                'default'    => 7.5,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('project_exchange_rates', true);

        $now = date('Y-m-d H:i:s');
        $this->db->table('project_exchange_rates')->insert([
            'label_year'  => '2026',
            'usd_ariary'  => 4500,
            'eur_ariary'  => 4900,
            'cny_ariary'  => 620,
            'jpy_ariary'  => 30,
            'fcfa_ariary' => 7.5,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('project_exchange_rates', true);
    }
}
