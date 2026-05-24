<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class MdgFokontanyModel extends Model
{
    protected $table            = 'mdg_fokontany';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['commune_id', 'name'];

    /**
     * @param list<int> $communeIds
     *
     * @return list<array{id: int, name: string, commune_id: int}>
     */
    public function listByCommuneIds(array $communeIds): array
    {
        if ($communeIds === []) {
            return [];
        }

        return $this->whereIn('commune_id', $communeIds)->orderBy('name', 'ASC')->findAll();
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

        $rows = $this->select('mdg_fokontany.id, mdg_fokontany.name, mdg_communes.name AS commune_name, mdg_districts.name AS district_name, mdg_regions.name AS region_name')
            ->join('mdg_communes', 'mdg_communes.id = mdg_fokontany.commune_id', 'left')
            ->join('mdg_districts', 'mdg_districts.id = mdg_communes.district_id', 'left')
            ->join('mdg_regions', 'mdg_regions.id = mdg_districts.region_id', 'left')
            ->whereIn('mdg_fokontany.id', $ids)
            ->orderBy('mdg_regions.name', 'ASC')
            ->orderBy('mdg_districts.name', 'ASC')
            ->orderBy('mdg_communes.name', 'ASC')
            ->orderBy('mdg_fokontany.name', 'ASC')
            ->findAll();

        $out = [];
        foreach ($rows as $row) {
            $parts = array_filter([
                trim((string) ($row['region_name'] ?? '')),
                trim((string) ($row['district_name'] ?? '')),
                trim((string) ($row['commune_name'] ?? '')),
                trim((string) ($row['name'] ?? '')),
            ]);

            $out[] = [
                'id'    => (int) $row['id'],
                'name'  => (string) ($row['name'] ?? ''),
                'label' => implode(' — ', $parts),
            ];
        }

        return $out;
    }
}
