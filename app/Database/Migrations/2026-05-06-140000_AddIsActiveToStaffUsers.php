<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsActiveToStaffUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('staff_users', [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('staff_users', 'is_active');
    }
}
