<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProjectContributionModel extends Model
{
    public const STATUS_NEW      = 'new';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_REJECTED = 'rejected';

    public const TYPE_BUDGET    = 'budget';
    public const TYPE_MATERIAL  = 'material';

    protected $table            = 'project_contributions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id',
        'project_slug',
        'project_title',
        'locale',
        'contribution_type',
        'donor_name',
        'contact',
        'donor_email',
        'amount',
        'items',
        'quantity',
        'available_from',
        'pickup_location',
        'can_deliver',
        'remarks',
        'ip_address',
        'status',
        'created_at',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps      = false;
}
