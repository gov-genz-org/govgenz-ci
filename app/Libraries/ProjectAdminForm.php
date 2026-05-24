<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\ProjectProjectModel;
use App\Models\SectorModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Validation, budget, corps et scripts du formulaire admin projet.
 */
final class ProjectAdminForm
{
    /**
     * @return array<string, string>
     */
    public static function validationRules(bool $isEdit): array
    {
        $statusList = implode(',', array_keys(ProjectProjectModel::projectStatusLabels()));
        $pubList    = implode(',', array_keys(ProjectProjectModel::publicationStateLabels()));

        $rules = [
            'slug'              => 'required|max_length[160]',
            'title'             => 'required|max_length[255]',
            'excerpt'           => 'permit_empty',
            'body'              => 'permit_empty',
            'body_content_mode' => 'required|in_list[html,blocks]',
            'project_status'    => 'required|in_list[' . $statusList . ']',
            'publication_state' => 'required|in_list[' . $pubList . ']',
            'volunteers_count'  => 'permit_empty|integer',
            'budget_amount'     => 'permit_empty|decimal',
            'budget_scale'      => 'permit_empty|in_list[' . implode(',', ProjectProjectModel::budgetScaleCodes()) . ']',
            'launched_at'       => 'permit_empty|valid_date',
            'duration_months'   => 'permit_empty|integer',
            'progress_percent'  => 'permit_empty|integer|less_than_equal_to[100]',
            'meta_title'        => 'permit_empty|max_length[255]',
            'meta_description'  => 'permit_empty|max_length[512]',
            'translation_group' => 'permit_empty|max_length[64]',
        ];

        if (! $isEdit) {
            $rules['locale'] = 'required|in_list[fr,en]';
        }

        return $rules;
    }

    public static function uniqueSlugForLocale(string $baseSlug, string $locale, ProjectProjectModel $model): string
    {
        $slug = LocaleSlug::normalizeSlug($baseSlug);
        if ($slug === '') {
            $slug = 'projet-' . $locale;
        }

        $candidate = $slug;
        $i         = 2;
        while ($model->where('slug', $candidate)->where('locale', $locale)->first() !== null) {
            $candidate = $slug . '-' . $i;
            $i++;
            if ($i > 500) {
                break;
            }
        }

        return $candidate;
    }

    /**
     * @return array<string, string>
     */
    public static function validateBudgetPost(IncomingRequest $request): array
    {
        $amountRaw = trim((string) $request->getPost('budget_amount'));
        if ($amountRaw === '') {
            return [];
        }

        $errors = [];
        if (! is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            $errors['budget_amount'] = lang('Admin.project_budget_amount_positive');
        }

        $scale = (string) $request->getPost('budget_scale');
        if (! in_array($scale, ProjectProjectModel::budgetScaleCodes(), true)) {
            $errors['budget_scale'] = lang('Admin.project_budget_scale_required');
        }

        return $errors;
    }

    /**
     * @return array{
     *   budget_amount: float|null,
     *   budget_scale: string|null,
     *   budget_ariary: int|null,
     *   budget_display: string|null
     * }
     */
    public static function budgetPayloadFromPost(IncomingRequest $request, string $locale): array
    {
        helper('project');

        $amountRaw = trim((string) $request->getPost('budget_amount'));
        if ($amountRaw === '' || ! is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            return [
                'budget_amount'  => null,
                'budget_scale'   => null,
                'budget_ariary'  => null,
                'budget_display' => null,
            ];
        }

        $scale = (string) $request->getPost('budget_scale');
        if (! in_array($scale, ProjectProjectModel::budgetScaleCodes(), true)) {
            $scale = ProjectProjectModel::BUDGET_SCALE_MILLION;
        }

        $amount = (float) $amountRaw;

        return [
            'budget_amount'  => $amount,
            'budget_scale'   => $scale,
            'budget_ariary'  => project_budget_ariary_from_parts($amount, $scale),
            'budget_display' => project_format_budget_display_from_parts($amount, $scale, $locale),
        ];
    }

    public static function sectorsCsvFromPost(IncomingRequest $request): string
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

    /**
     * @param array{bodyContentMode: string, canUseAdvancedHtml: bool} $formData
     */
    public static function editorScripts(array $formData): string
    {
        $scripts = '<script defer src="' . esc(base_url('js/admin/project-budget-preview.js'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-block-repeatable.js?v=6'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-budget-table-sync.js'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-geography-form.js'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-blocks-form.js'), 'attr') . '"></script>';
        if ($formData['canUseAdvancedHtml']) {
            $scripts = view('admin/partials/tinymce_init', [
                'uploadUrl'      => site_url('admin/media/upload'),
                'mediaJsonUrl'   => site_url('admin/media/json'),
                'pageUrlContact' => site_url('contact'),
                'pageUrlPress'   => site_url('press'),
                'editorSelector' => '#pp-body',
            ]) . view('admin/partials/form_dirty_guard') . $scripts;
        }

        return $scripts;
    }

    public static function nullableString(IncomingRequest $request, string $field): ?string
    {
        $v = trim((string) $request->getPost($field));

        return $v === '' ? null : $v;
    }

    public static function nullableDate(IncomingRequest $request, string $field): ?string
    {
        $v = trim((string) $request->getPost($field));

        return $v === '' ? null : $v;
    }

    public static function nullableUInt(IncomingRequest $request, string $field): ?int
    {
        $v = trim((string) $request->getPost($field));
        if ($v === '') {
            return null;
        }

        return max(0, (int) $v);
    }

    public static function nullableProgress(IncomingRequest $request, string $field): ?int
    {
        $v = trim((string) $request->getPost($field));
        if ($v === '') {
            return null;
        }

        return max(0, min(100, (int) $v));
    }

    /**
     * @return array{
     *   project: array<string, mixed>|null,
     *   sectors: list<array<string, mixed>>,
     *   blocksForForm: list<array<string, mixed>>,
     *   bodyContentMode: string,
     *   canUseAdvancedHtml: bool,
     *   bodyLockedLegacyHtml: bool,
     *   bodyStoredHtml: string,
     *   bodyOrphanHtml: bool,
     *   publicPreviewUrl: ?string
     * }
     */
    public static function formViewData(?array $project): array
    {
        helper('admin');

        $oldBlocks = old('blocks');
        if (is_array($oldBlocks)) {
            $blocksForForm = array_values($oldBlocks);
        } elseif ($project !== null) {
            $blocksForForm = ProjectBodyBlocksNormalizer::blocksForForm((string) ($project['body_blocks'] ?? ''));
        } else {
            $blocksForForm = [];
        }

        $existingMode = $project !== null ? strtolower(trim((string) ($project['body_content_mode'] ?? 'html'))) : 'blocks';
        if (! in_array($existingMode, ['html', 'blocks'], true)) {
            $existingMode = $project !== null ? 'html' : 'blocks';
        }

        $bodyStored   = $project !== null ? trim((string) ($project['body'] ?? '')) : '';
        $blocksStored = $project !== null ? trim((string) ($project['body_blocks'] ?? '')) : '';
        $hasBlocks    = $blocksStored !== '' && $blocksStored !== '[]';

        $defaultMode = $project === null ? 'blocks' : $existingMode;
        if ($project !== null && $bodyStored !== '' && ! $hasBlocks) {
            $defaultMode = 'html';
        }
        $bodyMode = old('body_content_mode', $defaultMode);
        if (! in_array($bodyMode, ['html', 'blocks'], true)) {
            $bodyMode = $defaultMode;
        }

        $canUseAdvancedHtml = ! admin_staff_is_editor_only();

        if (! $canUseAdvancedHtml) {
            $bodyMode = $existingMode === 'html' ? 'html' : 'blocks';
        }

        if ($project === null && $blocksForForm === [] && $bodyMode === 'blocks') {
            $blocksForForm = [
                [
                    'type'             => 'section_rich',
                    'heading'          => '',
                    'heading_style'    => 'warm',
                    'intro'            => '',
                    'bullets'          => [],
                    'extra_paragraphs' => [],
                ],
            ];
        }

        $previewUrl = null;
        if ($project !== null
            && (string) ($project['publication_state'] ?? '') === ProjectProjectModel::PUBLICATION_PUBLISHED
        ) {
            $previewUrl = admin_public_project_url(
                (string) ($project['slug'] ?? ''),
                (string) ($project['locale'] ?? 'fr'),
            );
        }

        return [
            'project'              => $project,
            'sectors'              => model(SectorModel::class)->listOrdered(),
            'blocksForForm'        => $blocksForForm,
            'bodyContentMode'      => $bodyMode,
            'canUseAdvancedHtml'   => $canUseAdvancedHtml,
            'bodyLockedLegacyHtml' => ! $canUseAdvancedHtml && $existingMode === 'html',
            'bodyStoredHtml'       => $bodyStored,
            'bodyOrphanHtml'       => $bodyStored !== '' && $hasBlocks && $existingMode === 'blocks',
            'publicPreviewUrl'     => $previewUrl,
        ];
    }

    /**
     * @param array<string, mixed>|null $existingProject
     *
     * @return array{body: ?string, body_content_mode: string, body_blocks: ?string}|null
     */
    public static function resolveBodyPayload(IncomingRequest $request, ?array $existingProject): ?array
    {
        helper('admin');

        if (admin_staff_is_editor_only()) {
            if ($existingProject !== null
                && strtolower(trim((string) ($existingProject['body_content_mode'] ?? ''))) === 'html'
            ) {
                return [
                    'body'              => $existingProject['body'] ?? null,
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
}
