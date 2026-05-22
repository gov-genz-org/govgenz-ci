<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\LocaleSlug;
use App\Libraries\ProjectBodyBlocksNormalizer;
use App\Libraries\ProjectBudgetTableSync;
use App\Libraries\ProjectGeographyPayload;
use App\Models\ProjectProjectModel;
use App\Models\SectorModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class ProjectProjects extends BaseController
{
    public function index()
    {
        $model = model(ProjectProjectModel::class);

        $loc = $this->request->getGet('loc');
        if (is_string($loc) && in_array($loc, ['fr', 'en'], true)) {
            $model = $model->where('locale', $loc);
        }

        $status = $this->request->getGet('status');
        if (is_string($status) && in_array($status, array_keys(ProjectProjectModel::projectStatusLabels()), true)) {
            $model = $model->where('project_status', $status);
        }

        $pub = $this->request->getGet('pub');
        if (is_string($pub) && in_array($pub, array_keys(ProjectProjectModel::publicationStateLabels()), true)) {
            $model = $model->where('publication_state', $pub);
        }

        $searchQuery = trim((string) $this->request->getGet('q'));
        if ($searchQuery !== '') {
            if (mb_strlen($searchQuery) > 120) {
                $searchQuery = mb_substr($searchQuery, 0, 120);
            }
            $model = $model->groupStart()->like('title', $searchQuery)->orLike('slug', $searchQuery)->groupEnd();
        }

        $list = $this->adminPaginatedList(
            $model,
            [
                'slug'               => 'slug',
                'locale'             => 'locale',
                'title'              => 'title',
                'project_status'     => 'project_status',
                'publication_state'  => 'publication_state',
                'updated_at'         => 'updated_at',
            ],
            'updated_at',
            'desc',
            ['status', 'pub', 'q', 'loc'],
        );

        return view('admin/layout', [
            'title' => 'Projets (programme)',
            'main'  => view('admin/project_projects/index', [
                'rows'                        => $list['rows'],
                'pager'                       => $list['pager'],
                'sort'                        => $list['sort'],
                'dir'                         => $list['dir'],
                'filterStatus'                => is_string($status) && in_array($status, array_keys(ProjectProjectModel::projectStatusLabels()), true) ? $status : 'all',
                'filterPub'                   => is_string($pub) && in_array($pub, array_keys(ProjectProjectModel::publicationStateLabels()), true) ? $pub : 'all',
                'filterLocale'                => is_string($loc) && in_array($loc, ['fr', 'en'], true) ? $loc : 'all',
                'searchQuery'                 => $searchQuery,
                'statusLabels'                => ProjectProjectModel::projectStatusLabels(),
                'pubLabels'                   => ProjectProjectModel::publicationStateLabels(),
                'translationLocalesByGroup' => $this->translationLocalesByGroupForRows($list['rows'], ProjectProjectModel::class),
            ]),
        ]);
    }

    public function create()
    {
        $formData = $this->projectFormViewData(null);

        return view('admin/layout', [
            'title'         => 'Nouveau projet',
            'main'          => view('admin/project_projects/form', $formData),
            'extraScripts'  => $this->projectFormScripts($formData),
        ]);
    }

    public function store(): ResponseInterface
    {
        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $budgetErrors = $this->validateBudgetPost();
        if ($budgetErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $budgetErrors);
        }

        $slug = LocaleSlug::normalizeSlug((string) $this->request->getPost('slug'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        $locale = LocaleSlug::normalizeLocale((string) $this->request->getPost('locale'));
        $budgetPayload = $this->budgetPayloadFromPost($locale);
        $geoPayload    = ProjectGeographyPayload::fromRequest($this->request);

        $model = model(ProjectProjectModel::class);
        if ($model->where('slug', $slug)->where('locale', $locale)->first() !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug est déjà utilisé pour cette langue.');
        }

        $bodyPayload = $this->resolveBodyPayload(null);
        if ($bodyPayload === null) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins un bloc valide.');
        }

        $merged        = ProjectBudgetTableSync::applyToPayloads($bodyPayload, $budgetPayload, $locale);
        $bodyPayload   = $merged['body'];
        $budgetPayload = $merged['budget'];

        $pubState = (string) $this->request->getPost('publication_state');
        $publishedAt = $pubState === ProjectProjectModel::PUBLICATION_PUBLISHED
            ? date('Y-m-d H:i:s')
            : null;

        $tgIn = trim((string) $this->request->getPost('translation_group'));

        $model->insert([
            'slug'               => $slug,
            'locale'             => $locale,
            'translation_group'  => $tgIn === '' ? null : $tgIn,
            'title'              => trim((string) $this->request->getPost('title')),
            'excerpt'            => $this->nullableString('excerpt'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'project_status'     => (string) $this->request->getPost('project_status'),
            'publication_state'  => $pubState,
            'sectors_csv'        => $this->sectorsCsvFromPost(),
            'volunteers_count'   => max(0, (int) $this->request->getPost('volunteers_count')),
            'budget_display'     => $budgetPayload['budget_display'],
            'budget_amount'      => $budgetPayload['budget_amount'],
            'budget_scale'       => $budgetPayload['budget_scale'],
            'budget_ariary'      => $budgetPayload['budget_ariary'],
            'geography'          => $geoPayload['geography'],
            'geography_data'     => $geoPayload['geography_data'],
            'launched_at'        => $this->nullableDate('launched_at'),
            'duration_months'    => $this->nullableUInt('duration_months'),
            'progress_percent'   => $this->nullableProgress('progress_percent'),
            'meta_title'         => $this->nullableString('meta_title'),
            'meta_description'   => $this->nullableString('meta_description'),
            'published_at'       => $publishedAt,
        ]);

        $newId = (int) $model->getInsertID();
        if ($newId > 0) {
            $tgFinal = $tgIn !== '' ? $tgIn : (string) $newId;
            $model->update($newId, ['translation_group' => $tgFinal]);
        }

        return $this->adminRedirectToEdit('admin/project-projects', $newId, 'Projet créé.');
    }

    public function edit(int $id): string
    {
        $project = model(ProjectProjectModel::class)->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $formData = $this->projectFormViewData($project);

        return view('admin/layout', [
            'title'         => 'Modifier le projet',
            'main'          => view('admin/project_projects/form', $formData),
            'extraScripts'  => $this->projectFormScripts($formData),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(ProjectProjectModel::class);
        $project = $model->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $budgetErrors = $this->validateBudgetPost();
        if ($budgetErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $budgetErrors);
        }

        $slug = LocaleSlug::normalizeSlug((string) $this->request->getPost('slug'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        $locale = LocaleSlug::normalizeLocale((string) ($project['locale'] ?? 'fr'));
        $budgetPayload = $this->budgetPayloadFromPost($locale);
        $geoPayload    = ProjectGeographyPayload::fromRequest($this->request);

        $other = $model->where('slug', $slug)->where('locale', $locale)->where('id !=', $id)->first();
        if ($other !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug est déjà utilisé pour cette langue.');
        }

        $bodyPayload = $this->resolveBodyPayload($project);
        if ($bodyPayload === null) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins un bloc valide.');
        }

        $merged        = ProjectBudgetTableSync::applyToPayloads($bodyPayload, $budgetPayload, $locale);
        $bodyPayload   = $merged['body'];
        $budgetPayload = $merged['budget'];

        $pubState = (string) $this->request->getPost('publication_state');
        $publishedAt = $project['published_at'] ?? null;
        if ($pubState === ProjectProjectModel::PUBLICATION_PUBLISHED && ($publishedAt === null || $publishedAt === '')) {
            $publishedAt = date('Y-m-d H:i:s');
        }
        if ($pubState === ProjectProjectModel::PUBLICATION_DRAFT) {
            $publishedAt = null;
        }

        $tgIn = trim((string) $this->request->getPost('translation_group'));
        if ($tgIn === '') {
            $tgIn = trim((string) ($project['translation_group'] ?? ''));
        }
        if ($tgIn === '') {
            $tgIn = (string) $id;
        }

        $model->update($id, [
            'slug'               => $slug,
            'translation_group'  => $tgIn,
            'title'              => trim((string) $this->request->getPost('title')),
            'excerpt'            => $this->nullableString('excerpt'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'project_status'     => (string) $this->request->getPost('project_status'),
            'publication_state'  => $pubState,
            'sectors_csv'        => $this->sectorsCsvFromPost(),
            'volunteers_count'   => max(0, (int) $this->request->getPost('volunteers_count')),
            'budget_display'     => $budgetPayload['budget_display'],
            'budget_amount'      => $budgetPayload['budget_amount'],
            'budget_scale'       => $budgetPayload['budget_scale'],
            'budget_ariary'      => $budgetPayload['budget_ariary'],
            'geography'          => $geoPayload['geography'],
            'geography_data'     => $geoPayload['geography_data'],
            'launched_at'        => $this->nullableDate('launched_at'),
            'duration_months'    => $this->nullableUInt('duration_months'),
            'progress_percent'   => $this->nullableProgress('progress_percent'),
            'meta_title'         => $this->nullableString('meta_title'),
            'meta_description'   => $this->nullableString('meta_description'),
            'published_at'       => $publishedAt,
        ]);

        return $this->adminRedirectToEdit('admin/project-projects', $id, 'Projet mis à jour.');
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(ProjectProjectModel::class);
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete($id);

        return redirect()->to(site_url('admin/project-projects'))->with('message', 'Projet supprimé.');
    }

    public function duplicate(int $id): ResponseInterface
    {
        helper('locale');
        $model = model(ProjectProjectModel::class);
        $src   = $model->find($id);
        if ($src === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $srcLocale    = LocaleSlug::normalizeLocale((string) ($src['locale'] ?? 'fr'));
        $targetLocale = $srcLocale === 'fr' ? 'en' : 'fr';

        $srcSlug = LocaleSlug::normalizeSlug((string) ($src['slug'] ?? ''));
        if ($srcSlug === '') {
            $srcSlug = 'projet';
        }

        $baseTargetSlug = $srcLocale === 'fr'
            ? locale_slug_fr_to_en($srcSlug)
            : locale_slug_en_to_fr($srcSlug);
        $baseTargetSlug = LocaleSlug::normalizeSlug($baseTargetSlug);
        if ($baseTargetSlug === '') {
            $baseTargetSlug = 'projet-' . $targetLocale;
        }

        $targetSlug = $this->buildUniqueSlugForProjectLocale($baseTargetSlug, $targetLocale, $model);

        $sourceGroup = trim((string) ($src['translation_group'] ?? ''));
        $group       = $sourceGroup !== '' ? $sourceGroup : (string) $id;
        if ($sourceGroup === '') {
            $model->update($id, ['translation_group' => $group]);
        }

        $partner = $model->where('translation_group', $group)->where('locale', $targetLocale)->first();
        if ($partner !== null) {
            return redirect()->to(site_url('admin/project-projects'))
                ->with('error', 'Une variante existe déjà pour cette langue dans ce groupe de traduction.');
        }

        $titleBase = trim((string) ($src['title'] ?? 'Sans titre'));
        $suffix    = $targetLocale === 'en' ? ' (EN)' : ' (FR)';
        if (mb_strlen($titleBase . $suffix) > 255) {
            $titleBase = mb_substr($titleBase, 0, max(1, 255 - mb_strlen($suffix)));
        }

        $model->insert([
            'slug'               => $targetSlug,
            'locale'             => $targetLocale,
            'translation_group'  => $group,
            'title'              => $titleBase . $suffix,
            'excerpt'            => $src['excerpt'] ?? null,
            'body'               => $src['body'] ?? null,
            'body_content_mode'  => (string) ($src['body_content_mode'] ?? 'html'),
            'body_blocks'        => $src['body_blocks'] ?? null,
            'project_status'     => (string) ($src['project_status'] ?? ProjectProjectModel::STATUS_CANDIDAT),
            'publication_state'  => ProjectProjectModel::PUBLICATION_DRAFT,
            'sectors_csv'        => (string) ($src['sectors_csv'] ?? ''),
            'volunteers_count'   => (int) ($src['volunteers_count'] ?? 0),
            'budget_display'     => $src['budget_display'] ?? null,
            'budget_amount'      => $src['budget_amount'] ?? null,
            'budget_scale'       => $src['budget_scale'] ?? null,
            'budget_ariary'      => $src['budget_ariary'] ?? null,
            'geography'          => $src['geography'] ?? null,
            'geography_data'     => $src['geography_data'] ?? null,
            'launched_at'        => $src['launched_at'] ?? null,
            'duration_months'    => $src['duration_months'] ?? null,
            'progress_percent'   => $src['progress_percent'] ?? null,
            'meta_title'         => $src['meta_title'] ?? null,
            'meta_description'   => $src['meta_description'] ?? null,
            'published_at'       => null,
        ]);

        $newId = (int) $model->getInsertID();

        return redirect()
            ->to(site_url('admin/project-projects/edit/' . $newId))
            ->with('message', 'Traduction créée en ' . strtoupper($targetLocale) . ' (brouillon).');
    }

    /**
     * Slug unique pour une locale donnée (équivalent admin pages).
     */
    private function buildUniqueSlugForProjectLocale(string $baseSlug, string $locale, ProjectProjectModel $model): string
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
    private function rules(bool $isEdit): array
    {
        $statusList = implode(',', array_keys(ProjectProjectModel::projectStatusLabels()));
        $pubList     = implode(',', array_keys(ProjectProjectModel::publicationStateLabels()));

        $rules = [
            'slug'               => 'required|max_length[160]',
            'title'              => 'required|max_length[255]',
            'excerpt'            => 'permit_empty',
            'body'               => 'permit_empty',
            'body_content_mode'  => 'required|in_list[html,blocks]',
            'project_status'     => 'required|in_list[' . $statusList . ']',
            'publication_state'  => 'required|in_list[' . $pubList . ']',
            'volunteers_count'   => 'permit_empty|integer',
            'budget_amount'      => 'permit_empty|decimal',
            'budget_scale'       => 'permit_empty|in_list[' . implode(',', ProjectProjectModel::budgetScaleCodes()) . ']',
            'launched_at'        => 'permit_empty|valid_date',
            'duration_months'    => 'permit_empty|integer',
            'progress_percent'   => 'permit_empty|integer|less_than_equal_to[100]',
            'meta_title'         => 'permit_empty|max_length[255]',
            'meta_description'   => 'permit_empty|max_length[512]',
            'translation_group'  => 'permit_empty|max_length[64]',
        ];

        if (! $isEdit) {
            $rules['locale'] = 'required|in_list[fr,en]';
        }

        return $rules;
    }

    private function nullableString(string $field): ?string
    {
        $v = trim((string) $this->request->getPost($field));

        return $v === '' ? null : $v;
    }

    private function nullableDate(string $field): ?string
    {
        $v = trim((string) $this->request->getPost($field));
        if ($v === '') {
            return null;
        }

        return $v;
    }

    private function nullableUInt(string $field): ?int
    {
        $v = trim((string) $this->request->getPost($field));
        if ($v === '') {
            return null;
        }

        return max(0, (int) $v);
    }

    private function nullableProgress(string $field): ?int
    {
        $v = trim((string) $this->request->getPost($field));
        if ($v === '') {
            return null;
        }
        $n = (int) $v;

        return max(0, min(100, $n));
    }

    /**
     * @return array<string, string>
     */
    private function validateBudgetPost(): array
    {
        $amountRaw = trim((string) $this->request->getPost('budget_amount'));
        if ($amountRaw === '') {
            return [];
        }

        $errors = [];
        if (! is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            $errors['budget_amount'] = 'Indiquez un montant numérique strictement positif.';
        }

        $scale = (string) $this->request->getPost('budget_scale');
        if (! in_array($scale, ProjectProjectModel::budgetScaleCodes(), true)) {
            $errors['budget_scale'] = 'Choisissez une unité explicite (millions, milliards, etc.).';
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
    private function budgetPayloadFromPost(string $locale): array
    {
        helper('project');

        $amountRaw = trim((string) $this->request->getPost('budget_amount'));
        if ($amountRaw === '' || ! is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            return [
                'budget_amount'  => null,
                'budget_scale'   => null,
                'budget_ariary'  => null,
                'budget_display' => null,
            ];
        }

        $scale = (string) $this->request->getPost('budget_scale');
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

    private function sectorsCsvFromPost(): string
    {
        $raw = $this->request->getPost('sectors');
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
        $out = array_values(array_unique($out));

        return implode(',', $out);
    }

    /**
     * @param array{bodyContentMode: string, canUseAdvancedHtml: bool} $formData
     */
    private function projectFormScripts(array $formData): string
    {
        $scripts = '<script defer src="' . esc(base_url('js/admin/project-budget-preview.js'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-block-repeatable.js?v=6'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-budget-table-sync.js'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-geography-form.js'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-blocks-form.js'), 'attr') . '"></script>';
        if ($formData['canUseAdvancedHtml']) {
            $scripts = $this->editorFormExtraScriptsForSelector('#pp-body') . $scripts;
        }

        return $scripts;
    }

    /**
     * @return array{
     *   project: array<string, mixed>|null,
     *   sectors: list<array<string, mixed>>,
     *   blocksForForm: list<array<string, mixed>>,
     *   bodyContentMode: string,
     *   canUseAdvancedHtml: bool,
     *   bodyLockedLegacyHtml: bool,
     *   publicPreviewUrl: ?string
     * }
     */
    private function projectFormViewData(?array $project): array
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
                    'type'          => 'section_rich',
                    'heading'       => '',
                    'heading_style' => 'warm',
                    'intro'         => '',
                    'bullets'       => [],
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
                (string) ($project['locale'] ?? 'fr')
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
    private function resolveBodyPayload(?array $existingProject): ?array
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

            $blocksJson = ProjectBodyBlocksNormalizer::bodyBlocksJsonIgnoringMode($this->request);
            if ($blocksJson === null || $blocksJson === '' || $blocksJson === '[]') {
                return null;
            }

            return [
                'body'              => null,
                'body_content_mode' => 'blocks',
                'body_blocks'       => $blocksJson,
            ];
        }

        $mode = ProjectBodyBlocksNormalizer::contentMode($this->request);
        $blocksJson = ProjectBodyBlocksNormalizer::bodyBlocksJson($this->request);
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
            'body'              => $this->nullableString('body'),
            'body_content_mode' => 'html',
            'body_blocks'       => null,
        ];
    }
}
