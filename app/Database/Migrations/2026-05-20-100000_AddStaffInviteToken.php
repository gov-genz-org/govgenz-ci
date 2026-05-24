<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStaffInviteToken extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('staff_users')) {
            return;
        }

        $this->forge->addColumn('staff_users', [
            'invite_token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'password_hash',
            ],
            'invite_token_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'invite_token_hash',
            ],
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('staff_users')) {
            return;
        }

        $this->forge->dropColumn('staff_users', ['invite_token_hash', 'invite_token_expires_at']);
    }
}
