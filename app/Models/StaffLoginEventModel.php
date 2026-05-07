<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class StaffLoginEventModel extends Model
{
    protected $table            = 'staff_login_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'staff_user_id',
        'email_attempt',
        'outcome',
        'detail',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = false;

}
