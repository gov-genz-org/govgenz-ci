<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\PositionItemModel;
use App\Models\ProjectProjectModel;
use App\Models\SectorModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Réponse JSON POST pour les filtres de listes programme (projets, positions).
 */
final class FrontProgramListFilter
{
    /**
     * @return array<string, mixed>
     */
    public static function projectsPayload(IncomingRequest $request): array
    {
        helper(['locale', 'cms', 'language', 'project']);

        $locale       = SiteContext::locale();
        $projectModel = model(ProjectProjectModel::class);
        $sectorModel  = model(SectorModel::class);

        $sectorOptionsNorm = SectorSelectOptions::normalizedForSelect($sectorModel);
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);
        $allowedSector     = array_keys($sectorFilterPills);

        $statusLabels = [
            ProjectProjectModel::STATUS_ACTIF      => lang('Projects.status_actif'),
            ProjectProjectModel::STATUS_CANDIDAT   => lang('Projects.status_candidat'),
            ProjectProjectModel::STATUS_VALIDATION => lang('Projects.status_validation'),
            ProjectProjectModel::STATUS_COMPLETE   => lang('Projects.status_complete'),
        ];
        $allowedStatus = array_keys($statusLabels);

        $payload = $request->getJSON(true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $filterStatuses = ProgramListFilter::sanitizeList($payload['status'] ?? null, $allowedStatus);
        $filterSectors  = ProgramListFilter::sanitizeList($payload['sector'] ?? null, $allowedSector);

        $allPublished = $projectModel->listPublishedRecent(100, $locale);
        $projects     = ProgramListFilter::filterByExactField($allPublished, $filterStatuses, 'project_status');
        $projects     = ProgramListFilter::filterBySectors($projects, $filterSectors);

        $listUrl = SiteContext::projectsPathPrefixEnabled()
            ? localized_site_url('projects')
            : localized_site_url('');

        return [
            'items'           => $projects,
            'shownCount'      => count($projects),
            'filtersActive'   => $filterStatuses !== [] || $filterSectors !== [],
            'listUrl'         => $listUrl,
            'sectorOptions'   => $sectorOptionsNorm,
            'sectorFilterPills' => $sectorFilterPills,
            'statusLabels'    => $statusLabels,
            'pillPrimary'     => $filterStatuses,
            'pillSecondary'   => $filterSectors,
            'gridMetaView'    => 'front/projects/partials/grid_meta',
            'gridInnerView'   => 'front/projects/partials/cards_grid',
            'gridMetaKey'     => 'projectsListUrl',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function positionsPayload(IncomingRequest $request): array
    {
        helper(['locale', 'language', 'position']);

        $locale      = SiteContext::locale();
        $itemModel   = model(PositionItemModel::class);
        $sectorModel = model(SectorModel::class);

        $sectorOptionsNorm = SectorSelectOptions::normalizedForSelect($sectorModel);
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);
        $allowedSector     = array_keys($sectorFilterPills);
        $typeLabels        = position_type_labels();
        $allowedTypes      = array_keys($typeLabels);

        $payload = $request->getJSON(true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $filterTypes   = ProgramListFilter::sanitizeList($payload['type'] ?? null, $allowedTypes);
        $filterSectors = ProgramListFilter::sanitizeList($payload['sector'] ?? null, $allowedSector);

        $allPublished = $itemModel->listPublishedRecent(100, $locale);
        $positions    = ProgramListFilter::filterByPositionTypes($allPublished, $filterTypes);
        $positions    = ProgramListFilter::filterBySectors($positions, $filterSectors);

        $listUrl = SiteContext::positionsPathPrefixEnabled()
            ? localized_site_url('positions')
            : localized_site_url('');

        return [
            'items'             => $positions,
            'shownCount'        => count($positions),
            'filtersActive'     => $filterTypes !== [] || $filterSectors !== [],
            'listUrl'           => $listUrl,
            'sectorOptions'     => $sectorOptionsNorm,
            'sectorFilterPills' => $sectorFilterPills,
            'typeLabels'        => $typeLabels,
            'pillPrimary'       => $filterTypes,
            'pillSecondary'     => $filterSectors,
            'gridMetaView'      => 'front/positions/partials/grid_meta',
            'gridInnerView'     => 'front/positions/partials/cards_timeline',
            'gridMetaKey'       => 'positionsListUrl',
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function jsonResponse(array $data): array
    {
        $listUrlKey = (string) ($data['gridMetaKey'] ?? 'listUrl');
        $itemsKey   = str_contains((string) ($data['gridInnerView'] ?? ''), 'positions') ? 'positions' : 'projects';

        $gridMetaHtml = view((string) $data['gridMetaView'], [
            'shownCount'           => $data['shownCount'],
            'filtersActive'        => $data['filtersActive'],
            $listUrlKey            => $data['listUrl'],
        ]);

        $gridInnerParams = [
            $itemsKey           => $data['items'],
            'sectorOptions'     => $data['sectorOptions'],
            'sectorFilterPills' => $data['sectorFilterPills'],
        ];
        if (isset($data['statusLabels'])) {
            $gridInnerParams['statusLabels'] = $data['statusLabels'];
        }
        if (isset($data['typeLabels'])) {
            $gridInnerParams['typeLabels'] = $data['typeLabels'];
        }

        $response = [
            'ok'            => true,
            'csrfHash'      => csrf_hash(),
            'gridMetaHtml'  => $gridMetaHtml,
            'gridInnerHtml' => view((string) $data['gridInnerView'], $gridInnerParams),
        ];

        if (str_contains((string) ($data['gridInnerView'] ?? ''), 'positions')) {
            $response['pillTypes']   = $data['pillPrimary'];
            $response['pillSectors'] = $data['pillSecondary'];
        } else {
            $response['pillStatus']  = $data['pillPrimary'];
            $response['pillSectors'] = $data['pillSecondary'];
        }

        return $response;
    }
}
