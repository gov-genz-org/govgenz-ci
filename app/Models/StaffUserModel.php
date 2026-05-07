<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class StaffUserModel extends Model
{
    protected $table            = 'staff_users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['email', 'password_hash', 'role', 'is_active', 'created_at', 'updated_at'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = true;
    protected $dateFormat         = 'datetime';
    protected $createdField       = 'created_at';
    protected $updatedField       = 'updated_at';
}
