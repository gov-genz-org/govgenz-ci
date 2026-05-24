<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\GeoCatalogCache;
use App\Models\MdgCommuneModel;
use App\Models\MdgDistrictModel;
use App\Models\MdgFokontanyModel;
use App\Models\MdgRegionModel;
use CodeIgniter\HTTP\ResponseInterface;

class GeoCatalog extends BaseController
{
    public function regions(): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError('Tables géographie absentes. Lancez : php spark migrate && php spark mdg:import-geo');
        }

        $items = GeoCatalogCache::remember('regions', function (): array {
            $out = [];
            foreach (model(MdgRegionModel::class)->listForSelect() as $row) {
                $out[] = [
                    'id'   => (int) $row['id'],
                    'name' => (string) $row['name'],
                ];
            }

            return $out;
        });

        return $this->response->setJSON(['ok' => true, 'items' => $items]);
    }

    public function districts(): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError('Tables géographie absentes.');
        }

        $regionIds = $this->intIdsFromRequest('region_ids');
        if ($regionIds === []) {
            return $this->response->setJSON(['ok' => true, 'items' => []]);
        }

        $items = GeoCatalogCache::remember(
            GeoCatalogCache::idsSuffix('districts', $regionIds),
            function () use ($regionIds): array {
                $out = [];
                foreach (model(MdgDistrictModel::class)->listByRegionIds($regionIds) as $row) {
                    $out[] = [
                        'id'        => (int) $row['id'],
                        'name'      => (string) $row['name'],
                        'region_id' => (int) $row['region_id'],
                    ];
                }

                return $out;
            },
        );

        return $this->response->setJSON(['ok' => true, 'items' => $items]);
    }

    public function communes(): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError('Tables géographie absentes.');
        }

        $districtIds = $this->intIdsFromRequest('district_ids');
        if ($districtIds === []) {
            return $this->response->setJSON(['ok' => true, 'items' => []]);
        }

        $items = GeoCatalogCache::remember(
            GeoCatalogCache::idsSuffix('communes', $districtIds),
            function () use ($districtIds): array {
                $out = [];
                foreach (model(MdgCommuneModel::class)->listByDistrictIds($districtIds) as $row) {
                    $out[] = [
                        'id'          => (int) $row['id'],
                        'name'        => (string) $row['name'],
                        'district_id' => (int) $row['district_id'],
                    ];
                }

                return $out;
            },
        );

        return $this->response->setJSON(['ok' => true, 'items' => $items]);
    }

    public function fokontany(): ResponseInterface
    {
        if (! $this->tablesReady()) {
            return $this->jsonError('Tables géographie absentes.');
        }

        $communeIds = $this->intIdsFromRequest('commune_ids');
        if ($communeIds === []) {
            return $this->response->setJSON(['ok' => true, 'items' => []]);
        }

        $items = GeoCatalogCache::remember(
            GeoCatalogCache::idsSuffix('fokontany', $communeIds),
            function () use ($communeIds): array {
                $out = [];
                foreach (model(MdgFokontanyModel::class)->listByCommuneIds($communeIds) as $row) {
                    $out[] = [
                        'id'         => (int) $row['id'],
                        'name'       => (string) $row['name'],
                        'commune_id' => (int) $row['commune_id'],
                    ];
                }

                return $out;
            },
        );

        return $this->response->setJSON(['ok' => true, 'items' => $items]);
    }

    private function tablesReady(): bool
    {
        return db_connect()->tableExists('mdg_regions');
    }

    /**
     * @return list<int>
     */
    private function intIdsFromRequest(string $key): array
    {
        $raw = $this->request->getGet($key);
        if (! is_array($raw)) {
            if ($raw === null || $raw === '') {
                return [];
            }
            $raw = [$raw];
        }

        $out = [];
        foreach ($raw as $v) {
            if (is_numeric($v)) {
                $id = (int) $v;
                if ($id > 0) {
                    $out[$id] = true;
                }
            }
        }

        return array_map('intval', array_keys($out));
    }

    private function jsonError(string $message): ResponseInterface
    {
        return $this->response->setStatusCode(503)->setJSON([
            'ok'    => false,
            'error' => $message,
            'items' => [],
        ]);
    }
}
