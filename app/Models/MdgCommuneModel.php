<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class MdgCommuneModel extends Model
{
    protected $table            = 'mdg_communes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'district_id', 'name'];

    /**
     * @param list<int> $districtIds
     *
     * @return list<array{id: int, name: string, district_id: int}>
     */
    public function listByDistrictIds(array $districtIds): array
    {
        if ($districtIds === []) {
            return [];
        }

        return $this->whereIn('district_id', $districtIds)->orderBy('name', 'ASC')->findAll();
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

        $rows = $this->select('mdg_communes.id, mdg_communes.name, mdg_districts.name AS district_name, mdg_regions.name AS region_name')
            ->join('mdg_districts', 'mdg_districts.id = mdg_communes.district_id', 'left')
            ->join('mdg_regions', 'mdg_regions.id = mdg_districts.region_id', 'left')
            ->whereIn('mdg_communes.id', $ids)
            ->orderBy('mdg_regions.name', 'ASC')
            ->orderBy('mdg_districts.name', 'ASC')
            ->orderBy('mdg_communes.name', 'ASC')
            ->findAll();

        $out = [];
        foreach ($rows as $row) {
            $region = trim((string) ($row['region_name'] ?? ''));
            $district = trim((string) ($row['district_name'] ?? ''));
            $commune = (string) ($row['name'] ?? '');
            $prefix = $region;
            if ($district !== '') {
                $prefix = $prefix !== '' ? $prefix . ' — ' . $district : $district;
            }

            $out[] = [
                'id'    => (int) $row['id'],
                'name'  => $commune,
                'label' => $prefix !== '' ? $prefix . ' — ' . $commune : $commune,
            ];
        }

        return $out;
    }
}
