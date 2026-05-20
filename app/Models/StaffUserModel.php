<?php

declare(strict_types=1);

namespace App\Models;

use App\Libraries\StaffInvite;
use CodeIgniter\Model;

class StaffUserModel extends Model
{
    protected $table            = 'staff_users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email',
        'password_hash',
        'invite_token_hash',
        'invite_token_expires_at',
        'role',
        'is_active',
        'notify_form_submissions',
        'created_at',
        'updated_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';

    /**
     * E-mails des comptes staff actifs (admin / éditeur), hors invitation non activée.
     *
     * @return list<string>
     */
    public function notificationEmailAddresses(): array
    {
        if (! $this->db->tableExists($this->table)) {
            return [];
        }

        $builder = $this->where('is_active', 1);
        if ($this->db->fieldExists('notify_form_submissions', $this->table)) {
            $builder->where('notify_form_submissions', 1);
        }

        $rows = $builder->findAll();
        $out  = [];

        foreach ($rows as $row) {
            if (StaffInvite::isPending($row)) {
                continue;
            }

            $email = mb_strtolower(trim((string) ($row['email'] ?? '')));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out[] = $email;
            }
        }

        return array_values(array_unique($out));
    }
}
