<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CmsMediaModel extends Model
{
    protected $table            = 'cms_media';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'stored_filename',
        'original_name',
        'mime_type',
        'size_bytes',
        'created_at',
    ];

    protected $useTimestamps = false;
}
