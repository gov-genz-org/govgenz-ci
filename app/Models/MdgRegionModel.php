<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class MdgRegionModel extends Model
{
    protected $table            = 'mdg_regions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'province_id', 'name'];

    /**
     * @return list<array{id: int, name: string}>
     */
    public function listForSelect(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }

    /**
     * @param list<int> $ids
     *
     * @return list<array{id: int, name: string}>
     */
    public function labelsByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return $this->whereIn('id', $ids)->orderBy('name', 'ASC')->findAll();
    }
}
