<?php

declare(strict_types=1);

namespace App\Controllers\Front\Projects;

use App\Controllers\BaseController;
use App\Controllers\Front\Traits\ProgramListFrontTrait;
use App\Libraries\CmsProgramListHero;
use App\Libraries\FrontPageAssets;
use App\Libraries\FrontProgramListFilter;
use App\Libraries\ProgramListFilter;
use App\Libraries\ProgramListProjectStats;
use App\Libraries\ProjectContributionSubmitter;
use App\Libraries\ProjectShareQrGenerator;
use App\Libraries\SectorSelectOptions;
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
    use ProgramListFrontTrait;

    public function index()
    {
        helper(['locale', 'cms', 'language', 'project']);

        $locale = SiteContext::locale();

        $projectModel = model(ProjectProjectModel::class);
        $sectorModel  = model(SectorModel::class);

        $sectorOptionsNorm = SectorSelectOptions::normalizedForSelect($sectorModel);
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
        $hero     = CmsProgramListHero::resolve(
            $listPage,
            lang('Projects.default_list_title'),
            lang('Projects.default_layout_title'),
        );

        $allPublished = $projectModel->listPublishedRecent(100, $locale);
        $projects     = $this->filterPublishedProjects($allPublished, $filterStatuses, $filterSectors);

        $stats = ProgramListProjectStats::forLocale($locale, $projectModel);

        $projectsListUrl = SiteContext::projectsPathPrefixEnabled()
            ? localized_site_url('projects')
            : localized_site_url('');

        $extraHead = FrontPageAssets::projectsProgramList();

        return view('front/layout', [
            'title'           => $hero['layoutTitle'],
            'metaDescription' => $hero['layoutMeta'],
            'extraHead'       => $extraHead,
            'main'            => view('front/projects/home', [
                'segments'           => SiteContext::publicUriSegments(),
                'projects'           => $projects,
                'sectorOptions'      => $sectorOptionsNorm,
                'sectorFilterPills'  => $sectorFilterPills,
                'statusLabels'       => $statusLabels,
                'filterStatuses'     => $filterStatuses,
                'filterSectors'      => $filterSectors,
                'stats'              => $stats,
                'projectsListUrl'    => $projectsListUrl,
                'filterPostUrl'      => projects_program_filter_post_url(),
                'csrfTokenName'      => csrf_token(),
                'csrfHash'           => csrf_hash(),
                'heroOverline'       => $hero['heroOverline'],
                'heroTitle'          => $hero['heroTitle'],
                'heroLead'           => $hero['heroLead'],
            ]),
            'navActive'       => 'projects',
            'mainExtraClass'  => $this->programListMainExtraClass($listPage),
        ]);
    }

    /**
     * Filtre liste projets (POST JSON, sans rechargement ni query string).
     */
    public function filterPost(): ResponseInterface
    {
        if ($reject = $this->rejectProgramListJsonFilter($this->request, $this->response)) {
            return $reject;
        }

        $payload = FrontProgramListFilter::projectsPayload($this->request);

        return $this->response->setJSON(FrontProgramListFilter::jsonResponse($payload));
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string>               $filterStatuses
     * @param list<string>               $filterSectors
     *
     * @return list<array<string, mixed>>
     */
    private function filterPublishedProjects(array $rows, array $filterStatuses, array $filterSectors): array
    {
        $rows = ProgramListFilter::filterByExactField($rows, $filterStatuses, 'project_status');

        return ProgramListFilter::filterBySectors($rows, $filterSectors);
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

        $shareUrl         = project_public_absolute_url($slug);
        $shareQrImageUrl  = project_share_qr_image_url($slug);
        $shareQrPageUrl   = project_share_qr_page_url($slug);

        $meta = trim((string) ($project['meta_description'] ?? ''));
        if ($meta === '') {
            $meta = trim((string) ($project['excerpt'] ?? ''));
        }

        $showFundBudget   = project_has_financial_funding($project);
        $showFundMaterial = project_has_material_needs($project);
        $showFundCta      = $showFundBudget || $showFundMaterial;

        $extraHead    = FrontPageAssets::projectsProgramShowHead();
        $extraScripts = FrontPageAssets::projectsProgramShowScripts($showFundCta);

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
                'shareUrl'         => $shareUrl,
                'shareQrImageUrl'  => $shareQrImageUrl,
                'shareQrPageUrl'   => $shareQrPageUrl,
                'currencyLines'    => project_currency_equivalents_for_project($project, $locale),
            ]),
            'navActive'       => 'projects',
            'mainExtraClass'  => 'ggz-layout-full',
            'extraScripts'    => $extraScripts,
        ]);
    }

    public function fundSubmit(string $slug): ResponseInterface
    {
        return ProjectContributionSubmitter::submit($this, $slug);
    }

    public function shareQrImage(string $slug): ResponseInterface
    {
        helper('project');
        $slug    = strtolower(trim($slug, '/'));
        $project = model(ProjectProjectModel::class)->findPublishedBySlugAnyLocale($slug);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $targetUrl = project_public_absolute_url($slug);

        try {
            $png = ProjectShareQrGenerator::generate($targetUrl, 512);
        } catch (\Throwable $e) {
            log_message('error', 'shareQrImage [{slug}]: {msg}', [
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
        helper(['language', 'project', 'locale']);
        $slug    = strtolower(trim($slug, '/'));
        $project = model(ProjectProjectModel::class)->findPublishedBySlugAnyLocale($slug);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $title         = (string) ($project['title'] ?? '');
        $qrImageUrl    = project_share_qr_image_url($slug);
        $projectUrl    = project_public_absolute_url($slug);
        $pageTitle     = lang('Projects.share_qr_page_title', ['title' => $title]);
        $ogTitle       = $pageTitle;
        $ogDescription = lang('Projects.share_qr_page_description', ['title' => $title]);

        $extraHead = '<meta property="og:type" content="website">'
            . '<meta property="og:title" content="' . esc($ogTitle, 'attr') . '">'
            . '<meta property="og:description" content="' . esc($ogDescription, 'attr') . '">'
            . '<meta property="og:image" content="' . esc($qrImageUrl, 'attr') . '">'
            . '<meta property="og:image:type" content="image/png">'
            . '<meta property="og:url" content="' . esc(project_share_qr_page_url($slug), 'attr') . '">'
            . '<meta name="twitter:card" content="summary_large_image">'
            . '<meta name="twitter:image" content="' . esc($qrImageUrl, 'attr') . '">'
            . '<link rel="stylesheet" href="' . esc(public_asset_url('assets/css/projects-program-show.css'), 'attr') . '">';

        return view('front/layout', [
            'title'           => $pageTitle,
            'metaDescription' => $ogDescription,
            'extraHead'       => $extraHead,
            'main'            => view('front/projects/share_qr', [
                'project'     => $project,
                'title'       => $title,
                'qrImageUrl'  => $qrImageUrl,
                'projectUrl'  => $projectUrl,
                'projectHref' => project_public_url($slug),
            ]),
            'navActive'      => 'projects',
            'mainExtraClass' => 'ggz-layout-full',
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
