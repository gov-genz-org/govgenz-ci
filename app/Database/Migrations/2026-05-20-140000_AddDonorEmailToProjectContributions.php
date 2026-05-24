<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDonorEmailToProjectContributions extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('project_contributions', [
            'donor_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
                'after'      => 'contact',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('project_contributions', 'donor_email');
    }
}
