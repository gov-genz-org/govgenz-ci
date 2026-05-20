<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotifyFormSubmissionsToStaffUsers extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('staff_users')) {
            return;
        }

        if ($this->db->fieldExists('notify_form_submissions', 'staff_users')) {
            return;
        }

        $this->forge->addColumn('staff_users', [
            'notify_form_submissions' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'after'      => 'is_active',
            ],
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('staff_users')) {
            return;
        }

        if ($this->db->fieldExists('notify_form_submissions', 'staff_users')) {
            $this->forge->dropColumn('staff_users', 'notify_form_submissions');
        }
    }
}
