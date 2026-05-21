<?php

declare(strict_types=1);

namespace App\Controllers\Front\Positions;

use App\Controllers\BaseController;
use App\Libraries\CmsProgramListHero;
use App\Libraries\ProgramListFilter;
use App\Libraries\ProjectShareQrGenerator;
use App\Libraries\SectorSelectOptions;
use App\Libraries\SiteContext;
use App\Models\CmsPageModel;
use App\Models\PositionItemModel;
use App\Models\SectorModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    public function index()
    {
        helper(['locale', 'cms', 'language', 'position']);

        $locale = SiteContext::locale();

        $itemModel   = model(PositionItemModel::class);
        $sectorModel = model(SectorModel::class);

        $sectorOptionsNorm = SectorSelectOptions::normalizedForSelect($sectorModel);
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);
        $typeLabels        = position_type_labels();

        $filterTypes   = [];
        $filterSectors = [];

        $listPage = model(CmsPageModel::class)->getPublishedBySlug(cms_positions_list_page_slug());
        $hero     = CmsProgramListHero::resolve(
            $listPage,
            lang('Positions.default_list_title'),
            lang('Positions.default_layout_title'),
        );

        $allPublished = $itemModel->listPublishedRecent(100, $locale);
        $positions    = $this->filterPublished($allPublished, $filterTypes, $filterSectors);

        $publishedCount = (int) $itemModel
            ->where('publication_state', PositionItemModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->countAllResults();

        $sectorCodesSeen = [];
        foreach ($allPublished as $r) {
            if (! is_array($r)) {
                continue;
            }
            foreach (array_filter(array_map('trim', explode(',', (string) ($r['sectors_csv'] ?? '')))) as $code) {
                $c = strtolower($code);
                if ($c !== '') {
                    $sectorCodesSeen[$c] = true;
                }
            }
        }

        $positionsListUrl = SiteContext::positionsPathPrefixEnabled()
            ? localized_site_url('positions')
            : localized_site_url('');

        $extraHead = '<link rel="stylesheet" href="' . esc(public_asset_url('assets/css/positions-program-list.css'), 'attr') . '">'
            . '<script defer src="' . esc(public_asset_url('js/front/positions-program-filters.js'), 'attr') . '"></script>';

        $mainExtra = $listPage !== null ? cms_layout_main_class($listPage['layout_key'] ?? null) : 'ggz-layout-full';
        if (trim($mainExtra) === '') {
            $mainExtra = 'ggz-layout-full';
        }

        return view('front/layout', [
            'title'           => $hero['layoutTitle'],
            'metaDescription' => $hero['layoutMeta'],
            'extraHead'       => $extraHead,
            'main'            => view('front/positions/home', [
                'positions'           => $positions,
                'sectorOptions'       => $sectorOptionsNorm,
                'sectorFilterPills'   => $sectorFilterPills,
                'typeLabels'          => $typeLabels,
                'filterTypes'         => $filterTypes,
                'filterSectors'       => $filterSectors,
                'stats'               => [
                    'published_count' => $publishedCount,
                    'sectors_covered' => count($sectorCodesSeen),
                    'types_count'     => count(PositionItemModel::typeCodes()),
                ],
                'positionsListUrl'    => $positionsListUrl,
                'filterPostUrl'       => positions_program_filter_post_url(),
                'csrfTokenName'       => csrf_token(),
                'csrfHash'            => csrf_hash(),
                'heroOverline'        => $hero['heroOverline'],
                'heroTitle'           => $hero['heroTitle'],
                'heroLead'            => $hero['heroLead'],
            ]),
            'navActive'      => 'positions',
            'mainExtraClass' => $mainExtra,
        ]);
    }

    public function filterPost(): ResponseInterface
    {
        helper(['locale', 'language', 'position']);

        $accept = $this->request->getHeaderLine('Accept');
        $xhr    = $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
        if (! $xhr && ! str_contains($accept, 'application/json')) {
            return $this->response->setStatusCode(406)->setJSON(['ok' => false, 'error' => 'json']);
        }

        $locale = SiteContext::locale();

        $itemModel   = model(PositionItemModel::class);
        $sectorModel = model(SectorModel::class);

        $sectorOptionsNorm = SectorSelectOptions::normalizedForSelect($sectorModel);
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);
        $allowedSector     = array_keys($sectorFilterPills);
        $typeLabels        = position_type_labels();
        $allowedTypes      = array_keys($typeLabels);

        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $filterTypes   = ProgramListFilter::sanitizeList($payload['type'] ?? null, $allowedTypes);
        $filterSectors = ProgramListFilter::sanitizeList($payload['sector'] ?? null, $allowedSector);

        $allPublished = $itemModel->listPublishedRecent(100, $locale);
        $positions    = $this->filterPublished($allPublished, $filterTypes, $filterSectors);
        $shownCount   = count($positions);
        $filtersActive = $filterTypes !== [] || $filterSectors !== [];

        $positionsListUrl = SiteContext::positionsPathPrefixEnabled()
            ? localized_site_url('positions')
            : localized_site_url('');

        $gridMetaHtml = view('front/positions/partials/grid_meta', [
            'shownCount'        => $shownCount,
            'filtersActive'     => $filtersActive,
            'positionsListUrl'  => $positionsListUrl,
        ]);

        $gridInnerHtml = view('front/positions/partials/cards_timeline', [
            'positions'          => $positions,
            'sectorOptions'      => $sectorOptionsNorm,
            'sectorFilterPills'  => $sectorFilterPills,
            'typeLabels'         => $typeLabels,
        ]);

        return $this->response->setJSON([
            'ok'            => true,
            'csrfHash'      => csrf_hash(),
            'gridMetaHtml'  => $gridMetaHtml,
            'gridInnerHtml' => $gridInnerHtml,
            'pillTypes'     => $filterTypes,
            'pillSectors'   => $filterSectors,
        ]);
    }

    public function tail(string $path)
    {
        helper(['locale', 'language', 'position']);
        $locale = SiteContext::locale();

        $path     = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (count($segments) === 1) {
            $slug = $segments[0];
            $item = model(PositionItemModel::class)->findPublishedBySlug($slug, $locale);
            if ($item !== null) {
                return $this->renderShow($item, $slug);
            }
            if (preg_match('/^[a-z0-9\-]+$/', $slug) === 1) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        throw PageNotFoundException::forPageNotFound();
    }

    public function shareQrImage(string $slug): ResponseInterface
    {
        helper('position');
        $slug = strtolower(trim($slug, '/'));
        $locale = SiteContext::locale();
        $item   = model(PositionItemModel::class)->findPublishedBySlug($slug, $locale);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $targetUrl = position_public_absolute_url($slug);

        try {
            $png = ProjectShareQrGenerator::generate($targetUrl, 512);
        } catch (\Throwable $e) {
            log_message('error', 'position shareQrImage [{slug}]: {msg}', [
                'slug' => $slug,
                'msg'  => $e->getMessage(),
            ]);
            throw PageNotFoundException::forPageNotFound();
        }

        $cacheMaxAge = ENVIRONMENT === 'development' ? 60 : 86400;

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'public, max-age=' . $cacheMaxAge)
            ->setBody($png);
    }

    public function shareQrPage(string $slug): string
    {
        helper(['language', 'position', 'locale']);
        $slug   = strtolower(trim($slug, '/'));
        $locale = SiteContext::locale();
        $item   = model(PositionItemModel::class)->findPublishedBySlug($slug, $locale);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $title      = (string) ($item['title'] ?? '');
        $qrImageUrl = position_share_qr_image_url($slug);
        $pageTitle  = lang('Projects.share_qr_page_title', ['title' => $title]);
        $ogDesc     = lang('Projects.share_qr_page_description', ['title' => $title]);

        $extraHead = '<meta property="og:type" content="website">'
            . '<meta property="og:title" content="' . esc($pageTitle, 'attr') . '">'
            . '<meta property="og:description" content="' . esc($ogDesc, 'attr') . '">'
            . '<meta property="og:image" content="' . esc($qrImageUrl, 'attr') . '">'
            . '<meta property="og:image:type" content="image/png">'
            . '<meta property="og:url" content="' . esc(position_share_qr_page_url($slug), 'attr') . '">'
            . '<meta name="twitter:card" content="summary_large_image">'
            . '<meta name="twitter:image" content="' . esc($qrImageUrl, 'attr') . '">'
            . '<link rel="stylesheet" href="' . esc(public_asset_url('assets/css/projects-program-show.css'), 'attr') . '">';

        return view('front/layout', [
            'title'           => $pageTitle,
            'metaDescription' => $ogDesc,
            'extraHead'       => $extraHead,
            'main'            => view('front/positions/share_qr', [
                'item'         => $item,
                'title'        => $title,
                'qrImageUrl'   => $qrImageUrl,
                'positionUrl'  => position_public_absolute_url($slug),
                'positionHref' => position_public_url($slug),
            ]),
            'navActive'      => 'positions',
            'mainExtraClass' => 'ggz-layout-full',
        ]);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function renderShow(array $item, string $slug): string
    {
        $locale = SiteContext::locale();
        $sectorModel = model(SectorModel::class);
        $sectorFilterPills  = $sectorModel->optionsForProjectFilterPills($locale);
        $sectorOptionsNorm  = SectorSelectOptions::normalizedForSelect($sectorModel);

        $meta = trim((string) ($item['meta_description'] ?? ''));
        if ($meta === '') {
            $meta = trim((string) ($item['excerpt'] ?? ''));
        }

        $positionsListUrl = SiteContext::positionsPathPrefixEnabled()
            ? localized_site_url('positions')
            : localized_site_url('');

        $itemId = (int) ($item['id'] ?? 0);
        $relatedPositions = $itemId > 0
            ? model(PositionItemModel::class)->listRelatedPublished(
                $itemId,
                $locale,
                (string) ($item['sectors_csv'] ?? ''),
                4,
            )
            : [];

        $shareUrl         = position_public_absolute_url($slug);
        $shareQrImageUrl  = position_share_qr_image_url($slug);
        $shareQrPageUrl   = position_share_qr_page_url($slug);

        $extraHead = '<link rel="stylesheet" href="' . esc(public_asset_url('assets/css/program-body-blocks.css'), 'attr') . '">'
            . '<link rel="stylesheet" href="' . esc(public_asset_url('assets/css/projects-program-show.css'), 'attr') . '">'
            . '<link rel="stylesheet" href="' . esc(public_asset_url('assets/css/positions-program-show.css'), 'attr') . '">';
        $extraScripts = '<script defer src="' . esc(public_asset_url('js/front/project-share.js'), 'attr') . '"></script>';

        return view('front/layout', [
            'title'           => trim((string) ($item['meta_title'] ?? '')) !== ''
                ? (string) $item['meta_title']
                : (string) ($item['title'] ?? lang('Positions.default_project_title')),
            'metaDescription' => $meta,
            'extraHead'       => $extraHead,
            'extraScripts'    => $extraScripts,
            'main'            => view('front/positions/show', [
                'item'               => $item,
                'slug'               => $slug,
                'sectorOptions'      => $sectorOptionsNorm,
                'sectorFilterPills'  => $sectorFilterPills,
                'typeLabels'         => position_type_labels(),
                'positionsListUrl'   => $positionsListUrl,
                'bodyHtml'           => position_body_html($item, $locale),
                'shareUrl'           => $shareUrl,
                'shareQrImageUrl'    => $shareQrImageUrl,
                'shareQrPageUrl'     => $shareQrPageUrl,
                'relatedPositions'   => $relatedPositions,
                'actionCtas'         => position_show_action_ctas((string) ($item['types_csv'] ?? ''), (string) ($item['title'] ?? '')),
            ]),
            'navActive'      => 'positions',
            'mainExtraClass' => 'ggz-layout-full',
        ]);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string>               $filterTypes
     * @param list<string>               $filterSectors
     *
     * @return list<array<string, mixed>>
     */
    private function filterPublished(array $rows, array $filterTypes, array $filterSectors): array
    {
        if ($filterTypes !== []) {
            $out = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $codes = position_types_from_csv((string) ($row['types_csv'] ?? ''));
                if (array_intersect($codes, $filterTypes) === []) {
                    continue;
                }
                $out[] = $row;
            }
            $rows = $out;
        }

        return ProgramListFilter::filterBySectors($rows, $filterSectors);
    }
}
