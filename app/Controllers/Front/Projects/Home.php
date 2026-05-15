<?php

declare(strict_types=1);

namespace App\Controllers\Front\Projects;

use App\Controllers\BaseController;
use App\Libraries\SiteContext;
use App\Models\CmsPageModel;
use App\Models\ProjectProjectModel;
use App\Models\SectorModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Front module « projects » — développement sous /projects (mono-domaine).
 * Plus tard : même contrôleurs possibles derrière le sous-domaine (contexte SiteContext).
 */
class Home extends BaseController
{
    public function index()
    {
        helper(['locale', 'cms', 'language', 'project']);

        $locale = SiteContext::locale();

        $projectModel = model(ProjectProjectModel::class);
        $sectorModel  = model(SectorModel::class);

        $sectorOptions = $sectorModel->optionsForSelect();
        $sectorOptionsNorm = [];
        foreach ($sectorOptions as $k => $v) {
            $sectorOptionsNorm[strtolower((string) $k)] = (string) $v;
        }
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);

        $statusLabels = [
            ProjectProjectModel::STATUS_ACTIF      => lang('Projects.status_actif'),
            ProjectProjectModel::STATUS_CANDIDAT   => lang('Projects.status_candidat'),
            ProjectProjectModel::STATUS_VALIDATION => lang('Projects.status_validation'),
            ProjectProjectModel::STATUS_COMPLETE   => lang('Projects.status_complete'),
        ];
        $filterStatuses = [];
        $filterSectors  = [];

        $listPage = model(CmsPageModel::class)->getPublishedBySlug(cms_projects_list_page_slug());

        $heroOverline = '';
        $heroTitle    = lang('Projects.default_list_title');
        $heroLead     = '';
        $layoutTitle  = lang('Projects.default_layout_title');
        $layoutMeta   = '';

        if ($listPage !== null) {
            $heroOverline = trim((string) ($listPage['hero_overline'] ?? ''));
            $ht           = trim((string) ($listPage['hero_title'] ?? ''));
            $heroTitle    = $ht !== '' ? $ht : trim((string) ($listPage['title'] ?? ''));
            if ($heroTitle === '') {
                $heroTitle = lang('Projects.default_list_title');
            }
            $heroLead = trim((string) ($listPage['hero_lead'] ?? ''));

            $mt = trim((string) ($listPage['meta_title'] ?? ''));
            if ($mt !== '') {
                $layoutTitle = $mt;
            }
            $md = trim((string) ($listPage['meta_description'] ?? ''));
            if ($md !== '') {
                $layoutMeta = $md;
            }
        }

        $allPublished = $projectModel->listPublishedRecent(100, $locale);
        $projects     = $this->filterPublishedProjects($allPublished, $filterStatuses, $filterSectors);

        $activeCount = (int) $projectModel
            ->where('publication_state', ProjectProjectModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->where('project_status', ProjectProjectModel::STATUS_ACTIF)
            ->countAllResults();

        $volRow = $projectModel
            ->selectSum('volunteers_count', 'vsum')
            ->where('publication_state', ProjectProjectModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->first();
        $volTotal = (int) ($volRow['vsum'] ?? 0);

        $aggRows = model(ProjectProjectModel::class)
            ->select('budget_ariary, budget_display, sectors_csv')
            ->where('publication_state', ProjectProjectModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->findAll();

        $budgetSumAriary = 0.0;
        $budgetParsed    = false;
        $sectorCodesSeen = [];
        foreach ($aggRows as $r) {
            if (! is_array($r)) {
                continue;
            }
            $parsed = project_budget_ariary_for_project($r);
            if ($parsed !== null) {
                $budgetSumAriary += $parsed;
                $budgetParsed = true;
            }
            foreach (array_filter(array_map('trim', explode(',', (string) ($r['sectors_csv'] ?? '')))) as $code) {
                $c = strtolower($code);
                if ($c !== '') {
                    $sectorCodesSeen[$c] = true;
                }
            }
        }
        $sectorsCoveredCount = count($sectorCodesSeen);
        $budgetTotalDisplay  = $budgetParsed
            ? project_format_budget_ariary_sum($budgetSumAriary, $locale)
            : lang('Projects.stats_value_emdash');

        $projectsListUrl = SiteContext::projectsPathPrefixEnabled()
            ? localized_site_url('projects')
            : localized_site_url('');

        $extraHead = '<link rel="stylesheet" href="' . esc(base_url('assets/css/projects-program-list.css'), 'attr') . '">'
            . '<link rel="stylesheet" href="' . esc(base_url('assets/css/project-geo-tooltip.css'), 'attr') . '">'
            . '<script defer src="' . esc(base_url('js/front/projects-program-filters.js'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/front/project-geo-tooltip.js'), 'attr') . '"></script>';

        $mainExtra = $listPage !== null ? cms_layout_main_class($listPage['layout_key'] ?? null) : 'ggz-layout-full';
        if (trim($mainExtra) === '') {
            $mainExtra = 'ggz-layout-full';
        }

        return view('front/layout', [
            'title'           => $layoutTitle,
            'metaDescription' => $layoutMeta,
            'extraHead'       => $extraHead,
            'main'            => view('front/projects/home', [
                'segments'           => SiteContext::publicUriSegments(),
                'projects'           => $projects,
                'sectorOptions'      => $sectorOptionsNorm,
                'sectorFilterPills'  => $sectorFilterPills,
                'statusLabels'       => $statusLabels,
                'filterStatuses'     => $filterStatuses,
                'filterSectors'      => $filterSectors,
                'stats'              => [
                    'active_projects'      => $activeCount,
                    'volunteers_sum'       => $volTotal,
                    'sectors_covered'      => $sectorsCoveredCount,
                    'budget_total_display' => $budgetTotalDisplay,
                ],
                'projectsListUrl'    => $projectsListUrl,
                'filterPostUrl'      => projects_program_filter_post_url(),
                'csrfTokenName'      => csrf_token(),
                'csrfHash'           => csrf_hash(),
                'heroOverline'       => $heroOverline,
                'heroTitle'          => $heroTitle,
                'heroLead'           => $heroLead,
            ]),
            'navActive'       => 'projects',
            'mainExtraClass'  => $mainExtra,
        ]);
    }

    /**
     * Filtre liste projets (POST JSON, sans rechargement ni query string).
     */
    public function filterPost(): ResponseInterface
    {
        helper(['locale', 'cms', 'language', 'project']);

        $accept = $this->request->getHeaderLine('Accept');
        $xhr     = $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
        if (! $xhr && ! str_contains($accept, 'application/json')) {
            return $this->response->setStatusCode(406)->setJSON(['ok' => false, 'error' => 'json']);
        }

        $locale = SiteContext::locale();

        $projectModel = model(ProjectProjectModel::class);
        $sectorModel  = model(SectorModel::class);

        $sectorOptions = $sectorModel->optionsForSelect();
        $sectorOptionsNorm = [];
        foreach ($sectorOptions as $k => $v) {
            $sectorOptionsNorm[strtolower((string) $k)] = (string) $v;
        }
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);
        $allowedSector     = array_keys($sectorFilterPills);

        $statusLabels = [
            ProjectProjectModel::STATUS_ACTIF      => lang('Projects.status_actif'),
            ProjectProjectModel::STATUS_CANDIDAT   => lang('Projects.status_candidat'),
            ProjectProjectModel::STATUS_VALIDATION => lang('Projects.status_validation'),
            ProjectProjectModel::STATUS_COMPLETE   => lang('Projects.status_complete'),
        ];
        $allowedStatus = array_keys($statusLabels);

        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $filterStatuses = $this->sanitizeFilterList($payload['status'] ?? null, $allowedStatus);
        $filterSectors  = $this->sanitizeFilterList($payload['sector'] ?? null, $allowedSector);

        $allPublished = $projectModel->listPublishedRecent(100, $locale);
        $projects     = $this->filterPublishedProjects($allPublished, $filterStatuses, $filterSectors);
        $shownCount   = count($projects);
        $filtersActive = $filterStatuses !== [] || $filterSectors !== [];

        $projectsListUrl = SiteContext::projectsPathPrefixEnabled()
            ? localized_site_url('projects')
            : localized_site_url('');

        $gridMetaHtml = view('front/projects/partials/grid_meta', [
            'shownCount'      => $shownCount,
            'filtersActive'   => $filtersActive,
            'projectsListUrl' => $projectsListUrl,
        ]);

        $gridInnerHtml = view('front/projects/partials/cards_grid', [
            'projects'           => $projects,
            'sectorOptions'      => $sectorOptionsNorm,
            'sectorFilterPills'  => $sectorFilterPills,
            'statusLabels'       => $statusLabels,
        ]);

        return $this->response->setJSON([
            'ok'             => true,
            'csrfHash'       => csrf_hash(),
            'gridMetaHtml'   => $gridMetaHtml,
            'gridInnerHtml'  => $gridInnerHtml,
            'pillStatus'     => $filterStatuses,
            'pillSectors'    => $filterSectors,
        ]);
    }

    /**
     * @param list<string> $allowed
     *
     * @return list<string>
     */
    private function sanitizeFilterList(mixed $raw, array $allowed): array
    {
        if ($allowed === []) {
            return [];
        }

        if ($raw === null || $raw === '') {
            return [];
        }

        if (! is_array($raw)) {
            $raw = [$raw];
        }

        $lookup = array_fill_keys($allowed, true);
        $seen   = [];
        foreach ($raw as $v) {
            $s = strtolower(trim((string) $v));
            if ($s === '' || ! isset($lookup[$s])) {
                continue;
            }
            $seen[$s] = true;
        }

        return array_keys($seen);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string>               $filterStatuses codes métier ; vide = pas de filtre sur le statut
     * @param list<string>               $filterSectors  codes secteurs (minuscules) ; vide = pas de filtre secteur
     *
     * @return list<array<string, mixed>>
     */
    private function filterPublishedProjects(array $rows, array $filterStatuses, array $filterSectors): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ($filterStatuses !== [] && ! in_array((string) ($row['project_status'] ?? ''), $filterStatuses, true)) {
                continue;
            }
            if ($filterSectors !== []) {
                $csv   = strtolower((string) ($row['sectors_csv'] ?? ''));
                $codes = array_filter(array_map('trim', explode(',', $csv)));
                $codes = array_map(static fn (string $c): string => strtolower($c), $codes);
                if (array_intersect($codes, $filterSectors) === []) {
                    continue;
                }
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $project
     */
    private function renderProjectShow(array $project, string $slug): string
    {
        helper(['locale', 'language', 'project', 'url']);
        $locale = SiteContext::locale();

        $sectorModel = model(SectorModel::class);
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);

        $status = (string) ($project['project_status'] ?? '');
        $statusLabels = [
            ProjectProjectModel::STATUS_ACTIF      => lang('Projects.status_actif'),
            ProjectProjectModel::STATUS_CANDIDAT   => lang('Projects.status_candidat'),
            ProjectProjectModel::STATUS_VALIDATION => lang('Projects.status_validation'),
            ProjectProjectModel::STATUS_COMPLETE   => lang('Projects.status_complete'),
        ];

        $relatedProjects = [];
        $others = model(ProjectProjectModel::class)
            ->where('publication_state', ProjectProjectModel::PUBLICATION_PUBLISHED)
            ->where('locale', $locale)
            ->where('slug !=', $slug)
            ->orderBy('published_at', 'DESC')
            ->orderBy('id', 'ASC')
            ->findAll(6);
        foreach ($others as $row) {
            if (! is_array($row)) {
                continue;
            }
            $s = (string) ($row['slug'] ?? '');
            if ($s === '') {
                continue;
            }
            $relatedProjects[] = [
                'slug'  => $s,
                'title' => (string) ($row['title'] ?? $s),
            ];
        }

        $projectsListUrl = SiteContext::projectsPathPrefixEnabled()
            ? localized_site_url('projects')
            : localized_site_url('');

        $shareUrl = project_public_url($slug);
        if (str_starts_with($shareUrl, '/')) {
            $shareUrl = rtrim((string) base_url(), '/') . $shareUrl;
        }

        $meta = trim((string) ($project['meta_description'] ?? ''));
        if ($meta === '') {
            $meta = trim((string) ($project['excerpt'] ?? ''));
        }

        $extraHead = '<link rel="stylesheet" href="' . esc(base_url('assets/css/projects-program-show.css'), 'attr') . '">'
            . '<link rel="stylesheet" href="' . esc(base_url('assets/css/project-geo-tooltip.css'), 'attr') . '">'
            . '<script defer src="' . esc(base_url('js/front/project-geo-tooltip.js'), 'attr') . '"></script>';

        return view('front/layout', [
            'title'           => trim((string) ($project['meta_title'] ?? '')) !== ''
                ? (string) $project['meta_title']
                : (string) ($project['title'] ?? lang('Projects.default_project_title')),
            'metaDescription' => $meta,
            'extraHead'       => $extraHead,
            'main'            => view('front/projects/show', [
                'project'           => $project,
                'sectorFilterPills' => $sectorFilterPills,
                'statusLabel'       => $statusLabels[$status] ?? $status,
                'statusBadge'       => project_status_badge_class($status),
                'launchedDisplay'   => project_format_launched_display(
                    isset($project['launched_at']) ? (string) $project['launched_at'] : null,
                    $locale
                ),
                'relatedProjects'   => $relatedProjects,
                'projectsListUrl'   => $projectsListUrl,
                'shareUrl'          => $shareUrl,
                'currencyLines'     => project_currency_equivalents_for_project($project, $locale),
            ]),
            'navActive'       => 'projects',
            'mainExtraClass'  => 'ggz-layout-full',
        ]);
    }

    /**
     * Chemin interne après /projects/ (un ou plusieurs segments).
     */
    public function tail(string $path)
    {
        helper('language');
        $locale = SiteContext::locale();

        $path = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (count($segments) === 1) {
            $slug = $segments[0];
            $project = model(ProjectProjectModel::class)->findPublishedBySlug($slug, $locale);
            if ($project !== null) {
                return $this->renderProjectShow($project, $slug);
            }
            if (preg_match('/^[a-z0-9\-]+$/', $slug) === 1) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $head = $path === '' ? '' : $segments[0];

        return view('front/layout', [
            'title'           => ($head !== '' ? $head . ' — ' : '') . lang('Projects.tail_layout_title_suffix'),
            'metaDescription' => '',
            'main'            => view('front/projects/tail', [
                'path'     => $path,
                'segments' => SiteContext::publicUriSegments(),
            ]),
            'navActive'       => 'projects',
            'mainExtraClass'  => '',
        ]);
    }
}
