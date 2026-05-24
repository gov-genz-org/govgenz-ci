<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\PositionItemModel;
use App\Models\ProjectProjectModel;
use App\Models\SectorModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Aperçu admin des fiches (positions / projets), aligné sur le rendu public.
 */
final class AdminRecordPreview
{
    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>|null
     */
    public static function mergePositionFromPost(array $item, IncomingRequest $request): ?array
    {
        helper('admin');

        $bodyPayload = self::resolvePositionBodyPayload($request, $item);
        if ($bodyPayload === null) {
            return null;
        }

        $slug = LocaleSlug::normalizeSlug((string) $request->getPost('slug'));
        if ($slug === '') {
            $slug = (string) ($item['slug'] ?? '');
        }

        return array_merge($item, [
            'slug'               => $slug,
            'title'              => trim((string) $request->getPost('title')),
            'excerpt'            => self::nullableString($request, 'excerpt'),
            'summary'            => self::nullableString($request, 'summary'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'types_csv'          => self::positionTypesCsvFromPost($request),
            'sectors_csv'        => self::sectorsCsvFromPost($request),
            'reading_minutes'    => self::nullableUInt($request, 'reading_minutes'),
            'publication_state'  => (string) $request->getPost('publication_state'),
            'meta_title'         => self::nullableString($request, 'meta_title'),
            'meta_description'   => self::nullableString($request, 'meta_description'),
        ]);
    }

    /**
     * @param array<string, mixed> $project
     *
     * @return array<string, mixed>|null
     */
    public static function mergeProjectFromPost(array $project, IncomingRequest $request): ?array
    {
        $locale = LocaleSlug::normalizeLocale((string) ($project['locale'] ?? 'fr'));
        $budgetPayload = ProjectAdminForm::budgetPayloadFromPost($request, $locale);
        $geoPayload    = ProjectGeographyPayload::fromRequest($request);

        $bodyPayload = ProjectAdminForm::resolveBodyPayload($request, $project);
        if ($bodyPayload === null) {
            return null;
        }

        $merged        = ProjectBudgetTableSync::applyToPayloads($bodyPayload, $budgetPayload, $locale);
        $bodyPayload   = $merged['body'];
        $budgetPayload = $merged['budget'];

        $slug = LocaleSlug::normalizeSlug((string) $request->getPost('slug'));
        if ($slug === '') {
            $slug = (string) ($project['slug'] ?? '');
        }

        return array_merge($project, [
            'slug'               => $slug,
            'title'              => trim((string) $request->getPost('title')),
            'excerpt'            => ProjectAdminForm::nullableString($request, 'excerpt'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'project_status'     => (string) $request->getPost('project_status'),
            'publication_state'  => (string) $request->getPost('publication_state'),
            'sectors_csv'        => ProjectAdminForm::sectorsCsvFromPost($request),
            'volunteers_count'   => max(0, (int) $request->getPost('volunteers_count')),
            'budget_display'     => $budgetPayload['budget_display'],
            'budget_amount'      => $budgetPayload['budget_amount'],
            'budget_scale'       => $budgetPayload['budget_scale'],
            'budget_ariary'      => $budgetPayload['budget_ariary'],
            'geography'          => $geoPayload['geography'],
            'geography_data'     => $geoPayload['geography_data'],
            'launched_at'        => ProjectAdminForm::nullableDate($request, 'launched_at'),
            'duration_months'    => ProjectAdminForm::nullableUInt($request, 'duration_months'),
            'progress_percent'   => ProjectAdminForm::nullableProgress($request, 'progress_percent'),
            'meta_title'         => ProjectAdminForm::nullableString($request, 'meta_title'),
            'meta_description'   => ProjectAdminForm::nullableString($request, 'meta_description'),
        ]);
    }

    /**
     * @param array<string, mixed> $item
     */
    public static function renderPosition(array $item, string $previewRibbon): string
    {
        helper(['locale', 'language', 'position', 'url']);

        $locale = (($item['locale'] ?? 'fr') === 'en') ? 'en' : 'fr';
        SiteContext::setLocale($locale);

        $slug = strtolower(trim((string) ($item['slug'] ?? '')));
        if ($slug === '') {
            $slug = 'preview';
        }

        $sectorModel       = model(SectorModel::class);
        $sectorFilterPills = $sectorModel->optionsForProjectFilterPills($locale);
        $sectorOptionsNorm = SectorSelectOptions::normalizedForSelect($sectorModel);

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

        $shareUrl        = position_public_absolute_url($slug);
        $shareQrImageUrl = position_share_qr_image_url($slug);
        $shareQrPageUrl  = position_share_qr_page_url($slug);
        $positionAssets  = FrontPageAssets::positionsProgramShow();

        $titlePrefix = lang('Admin.preview_title_prefix');
        $pageTitle   = trim((string) ($item['meta_title'] ?? '')) !== ''
            ? (string) $item['meta_title']
            : (string) ($item['title'] ?? lang('Positions.default_project_title'));

        return view('front/layout', [
            'title'           => $titlePrefix . $pageTitle,
            'metaDescription' => $meta,
            'extraHead'       => $positionAssets['head'],
            'extraScripts'    => $positionAssets['scripts'],
            'main'            => view('front/positions/show', [
                'item'              => $item,
                'slug'              => $slug,
                'sectorOptions'     => $sectorOptionsNorm,
                'sectorFilterPills' => $sectorFilterPills,
                'typeLabels'        => position_type_labels(),
                'positionsListUrl'  => $positionsListUrl,
                'bodyHtml'          => position_body_html($item, $locale),
                'shareUrl'          => $shareUrl,
                'shareQrImageUrl'   => $shareQrImageUrl,
                'shareQrPageUrl'    => $shareQrPageUrl,
                'relatedPositions'  => $relatedPositions,
                'actionCtas'        => position_show_action_ctas((string) ($item['types_csv'] ?? ''), (string) ($item['title'] ?? '')),
            ]),
            'navActive'      => 'positions',
            'mainExtraClass' => 'ggz-layout-full',
            'previewRibbon'  => $previewRibbon,
        ]);
    }

    /**
     * @param array<string, mixed> $project
     */
    public static function renderProject(array $project, string $previewRibbon): string
    {
        helper(['locale', 'language', 'project', 'url']);

        $locale = (($project['locale'] ?? 'fr') === 'en') ? 'en' : 'fr';
        SiteContext::setLocale($locale);

        $slug = strtolower(trim((string) ($project['slug'] ?? '')));
        if ($slug === '') {
            $slug = 'preview';
        }

        $sectorModel       = model(SectorModel::class);
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

        $shareUrl        = project_public_absolute_url($slug);
        $shareQrImageUrl = project_share_qr_image_url($slug);
        $shareQrPageUrl  = project_share_qr_page_url($slug);

        $meta = trim((string) ($project['meta_description'] ?? ''));
        if ($meta === '') {
            $meta = trim((string) ($project['excerpt'] ?? ''));
        }

        $showFundBudget   = project_has_financial_funding($project);
        $showFundMaterial = project_has_material_needs($project);
        $showFundCta      = $showFundBudget || $showFundMaterial;

        $extraHead    = FrontPageAssets::projectsProgramShowHead();
        $extraScripts = FrontPageAssets::projectsProgramShowScripts($showFundCta);

        $titlePrefix = lang('Admin.preview_title_prefix');
        $pageTitle   = trim((string) ($project['meta_title'] ?? '')) !== ''
            ? (string) $project['meta_title']
            : (string) ($project['title'] ?? lang('Projects.default_project_title'));

        return view('front/layout', [
            'title'           => $titlePrefix . $pageTitle,
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
                'projectsListUrl'     => $projectsListUrl,
                'shareUrl'            => $shareUrl,
                'shareQrImageUrl'     => $shareQrImageUrl,
                'shareQrPageUrl'      => $shareQrPageUrl,
                'currencyLines'       => project_currency_equivalents_for_project($project, $locale),
            ]),
            'navActive'      => 'projects',
            'mainExtraClass' => 'ggz-layout-full',
            'extraScripts'   => $extraScripts,
            'previewRibbon'  => $previewRibbon,
        ]);
    }

    /**
     * @param array<string, mixed>|null $existing
     *
     * @return array{body: ?string, body_content_mode: string, body_blocks: ?string}|null
     */
    private static function resolvePositionBodyPayload(IncomingRequest $request, ?array $existing): ?array
    {
        helper('admin');

        if (admin_staff_is_editor_only()) {
            if ($existing !== null
                && strtolower(trim((string) ($existing['body_content_mode'] ?? ''))) === 'html'
            ) {
                return [
                    'body'              => $existing['body'] ?? null,
                    'body_content_mode' => 'html',
                    'body_blocks'       => null,
                ];
            }

            $blocksJson = ProjectBodyBlocksNormalizer::bodyBlocksJsonIgnoringMode($request);
            if ($blocksJson === null || $blocksJson === '' || $blocksJson === '[]') {
                return null;
            }

            return [
                'body'              => null,
                'body_content_mode' => 'blocks',
                'body_blocks'       => $blocksJson,
            ];
        }

        $mode       = ProjectBodyBlocksNormalizer::contentMode($request);
        $blocksJson = ProjectBodyBlocksNormalizer::bodyBlocksJson($request);
        if ($mode === 'blocks') {
            if ($blocksJson === null || $blocksJson === '' || $blocksJson === '[]') {
                return null;
            }

            return [
                'body'              => null,
                'body_content_mode' => 'blocks',
                'body_blocks'       => $blocksJson,
            ];
        }

        return [
            'body'              => self::nullableString($request, 'body'),
            'body_content_mode' => 'html',
            'body_blocks'       => null,
        ];
    }

    private static function nullableString(IncomingRequest $request, string $field): ?string
    {
        $v = trim((string) $request->getPost($field));

        return $v === '' ? null : $v;
    }

    private static function nullableUInt(IncomingRequest $request, string $field): ?int
    {
        $v = trim((string) $request->getPost($field));
        if ($v === '') {
            return null;
        }

        return max(0, (int) $v);
    }

    private static function sectorsCsvFromPost(IncomingRequest $request): string
    {
        $raw = $request->getPost('sectors');
        if (! is_array($raw)) {
            return '';
        }
        $allowed = [];
        foreach (model(SectorModel::class)->listOrdered() as $row) {
            $c = strtolower(trim((string) ($row['code'] ?? '')));
            if ($c !== '') {
                $allowed[$c] = true;
            }
        }
        $out = [];
        foreach ($raw as $code) {
            if (! is_string($code)) {
                continue;
            }
            $code = strtolower(trim($code));
            if ($code !== '' && isset($allowed[$code])) {
                $out[] = $code;
            }
        }

        return implode(',', array_values(array_unique($out)));
    }

    private static function positionTypesCsvFromPost(IncomingRequest $request): string
    {
        $raw = $request->getPost('types');
        if (! is_array($raw)) {
            return '';
        }
        $allowed = array_fill_keys(PositionItemModel::typeCodes(), true);
        $out     = [];
        foreach ($raw as $code) {
            if (! is_string($code)) {
                continue;
            }
            $code = strtolower(trim($code));
            if ($code !== '' && isset($allowed[$code])) {
                $out[] = $code;
            }
        }

        return implode(',', array_values(array_unique($out)));
    }
}
