<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class VolunteerApplicationModel extends Model
{
    protected $table            = 'volunteer_applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sector',
        'full_name',
        'email',
        'phone',
        'message',
        'ip_address',
        'status',
        'created_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = false;
}
