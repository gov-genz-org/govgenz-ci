<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class MdgDistrictModel extends Model
{
    protected $table            = 'mdg_districts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'region_id', 'name'];

    /**
     * @param list<int> $regionIds
     *
     * @return list<array{id: int, name: string, region_id: int}>
     */
    public function listByRegionIds(array $regionIds): array
    {
        if ($regionIds === []) {
            return [];
        }

        return $this->whereIn('region_id', $regionIds)->orderBy('name', 'ASC')->findAll();
    }

    /**
     * @param list<int> $ids
     *
     * @return list<array{id: int, name: string, label: string}>
     */
    public function labelsByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rows = $this->select('mdg_districts.id, mdg_districts.name, mdg_regions.name AS region_name')
            ->join('mdg_regions', 'mdg_regions.id = mdg_districts.region_id', 'left')
            ->whereIn('mdg_districts.id', $ids)
            ->orderBy('mdg_regions.name', 'ASC')
            ->orderBy('mdg_districts.name', 'ASC')
            ->findAll();

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id'    => (int) $row['id'],
                'name'  => (string) $row['name'],
                'label' => trim((string) ($row['region_name'] ?? '')) !== ''
                    ? (string) $row['region_name'] . ' — ' . (string) $row['name']
                    : (string) $row['name'],
            ];
        }

        return $out;
    }
}
