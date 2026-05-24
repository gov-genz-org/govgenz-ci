<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class SiteNavItemModel extends Model
{
    protected $table            = 'site_nav_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'locale',
        'sort_order',
        'label',
        'href_kind',
        'href_target',
        'match_key',
        'css_class',
        'is_active',
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
     * Entrées visibles pour le menu public, triées.
     *
     * @return list<array<string, mixed>>
     */
    public function listActiveOrdered(string $locale = 'fr'): array
    {
        $builder = $this->where('is_active', 1);

        if ($this->db->fieldExists('locale', $this->table)) {
            $builder = $builder->where('locale', $locale);
        }

        return $builder->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
