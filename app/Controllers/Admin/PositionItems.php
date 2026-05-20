<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ProjectBodyBlocksNormalizer;
use App\Models\PositionItemModel;
use App\Models\SectorModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class PositionItems extends BaseController
{
    public function index()
    {
        $model = model(PositionItemModel::class);

        $loc = $this->request->getGet('loc');
        if (is_string($loc) && in_array($loc, ['fr', 'en'], true)) {
            $model = $model->where('locale', $loc);
        }

        $pub = $this->request->getGet('pub');
        if (is_string($pub) && in_array($pub, array_keys(PositionItemModel::publicationStateLabels()), true)) {
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
                'slug'              => 'slug',
                'locale'            => 'locale',
                'title'             => 'title',
                'publication_state' => 'publication_state',
                'updated_at'        => 'updated_at',
            ],
            'updated_at',
            'desc',
            ['pub', 'q', 'loc'],
        );

        return view('admin/layout', [
            'title' => 'Positions (programme)',
            'main'  => view('admin/position_items/index', [
                'rows'                        => $list['rows'],
                'pager'                       => $list['pager'],
                'sort'                        => $list['sort'],
                'dir'                         => $list['dir'],
                'filterPub'                   => is_string($pub) && in_array($pub, array_keys(PositionItemModel::publicationStateLabels()), true) ? $pub : 'all',
                'filterLocale'                => is_string($loc) && in_array($loc, ['fr', 'en'], true) ? $loc : 'all',
                'searchQuery'                 => $searchQuery,
                'pubLabels'                   => PositionItemModel::publicationStateLabels(),
                'translationLocalesByGroup' => $this->translationLocalesByGroupForRows($list['rows'], PositionItemModel::class),
            ]),
        ]);
    }

    public function create()
    {
        $formData = $this->positionFormViewData(null);

        return view('admin/layout', [
            'title'        => 'Nouvelle position',
            'main'         => view('admin/position_items/form', $formData),
            'extraScripts' => $this->positionFormScripts($formData),
        ]);
    }

    public function store(): ResponseInterface
    {
        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug = $this->normalizeSlug((string) $this->request->getPost('slug'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        $locale = $this->normalizeLocale((string) $this->request->getPost('locale'));
        $model  = model(PositionItemModel::class);
        if ($model->where('slug', $slug)->where('locale', $locale)->first() !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug est déjà utilisé pour cette langue.');
        }

        $bodyPayload = $this->resolveBodyPayload(null);
        if ($bodyPayload === null) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins un bloc valide.');
        }

        $pubState    = (string) $this->request->getPost('publication_state');
        $publishedAt = $pubState === PositionItemModel::PUBLICATION_PUBLISHED ? date('Y-m-d H:i:s') : null;
        $tgIn        = trim((string) $this->request->getPost('translation_group'));

        $model->insert([
            'slug'               => $slug,
            'locale'             => $locale,
            'translation_group'  => $tgIn === '' ? null : $tgIn,
            'title'              => trim((string) $this->request->getPost('title')),
            'excerpt'            => $this->nullableString('excerpt'),
            'summary'            => $this->nullableString('summary'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'types_csv'          => $this->typesCsvFromPost(),
            'sectors_csv'        => $this->sectorsCsvFromPost(),
            'reading_minutes'    => $this->nullableUInt('reading_minutes'),
            'publication_state'  => $pubState,
            'meta_title'         => $this->nullableString('meta_title'),
            'meta_description'   => $this->nullableString('meta_description'),
            'published_at'       => $publishedAt,
        ]);

        $newId = (int) $model->getInsertID();
        if ($newId > 0) {
            $tgFinal = $tgIn !== '' ? $tgIn : (string) $newId;
            $model->update($newId, ['translation_group' => $tgFinal]);
        }

        return $this->adminRedirectToEdit('admin/position-items', $newId, 'Position créée.');
    }

    public function edit(int $id): string
    {
        $item = model(PositionItemModel::class)->find($id);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $formData = $this->positionFormViewData($item);

        return view('admin/layout', [
            'title'        => 'Modifier la position',
            'main'         => view('admin/position_items/form', $formData),
            'extraScripts' => $this->positionFormScripts($formData),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(PositionItemModel::class);
        $item  = $model->find($id);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug = $this->normalizeSlug((string) $this->request->getPost('slug'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        $locale = $this->normalizeLocale((string) ($item['locale'] ?? 'fr'));
        if ($model->where('slug', $slug)->where('locale', $locale)->where('id !=', $id)->first() !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug est déjà utilisé pour cette langue.');
        }

        $bodyPayload = $this->resolveBodyPayload($item);
        if ($bodyPayload === null) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins un bloc valide.');
        }

        $pubState    = (string) $this->request->getPost('publication_state');
        $publishedAt = $item['published_at'] ?? null;
        if ($pubState === PositionItemModel::PUBLICATION_PUBLISHED && ($publishedAt === null || $publishedAt === '')) {
            $publishedAt = date('Y-m-d H:i:s');
        }
        if ($pubState === PositionItemModel::PUBLICATION_DRAFT) {
            $publishedAt = null;
        }

        $tgIn = trim((string) $this->request->getPost('translation_group'));
        if ($tgIn === '') {
            $tgIn = trim((string) ($item['translation_group'] ?? ''));
        }
        if ($tgIn === '') {
            $tgIn = (string) $id;
        }

        $model->update($id, [
            'slug'               => $slug,
            'translation_group'  => $tgIn,
            'title'              => trim((string) $this->request->getPost('title')),
            'excerpt'            => $this->nullableString('excerpt'),
            'summary'            => $this->nullableString('summary'),
            'body'               => $bodyPayload['body'],
            'body_content_mode'  => $bodyPayload['body_content_mode'],
            'body_blocks'        => $bodyPayload['body_blocks'],
            'types_csv'          => $this->typesCsvFromPost(),
            'sectors_csv'        => $this->sectorsCsvFromPost(),
            'reading_minutes'    => $this->nullableUInt('reading_minutes'),
            'publication_state'  => $pubState,
            'meta_title'         => $this->nullableString('meta_title'),
            'meta_description'   => $this->nullableString('meta_description'),
            'published_at'       => $publishedAt,
        ]);

        return $this->adminRedirectToEdit('admin/position-items', $id, 'Position mise à jour.');
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(PositionItemModel::class);
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete($id);

        return redirect()->to(site_url('admin/position-items'))->with('message', 'Position supprimée.');
    }

    public function duplicate(int $id): ResponseInterface
    {
        helper('locale');
        $model = model(PositionItemModel::class);
        $src   = $model->find($id);
        if ($src === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $srcLocale    = $this->normalizeLocale((string) ($src['locale'] ?? 'fr'));
        $targetLocale = $srcLocale === 'fr' ? 'en' : 'fr';
        $srcSlug      = $this->normalizeSlug((string) ($src['slug'] ?? ''));
        if ($srcSlug === '') {
            $srcSlug = 'position';
        }

        $baseTargetSlug = $srcLocale === 'fr'
            ? locale_slug_fr_to_en($srcSlug)
            : locale_slug_en_to_fr($srcSlug);
        $baseTargetSlug = $this->normalizeSlug($baseTargetSlug);
        if ($baseTargetSlug === '') {
            $baseTargetSlug = 'position-' . $targetLocale;
        }

        $targetSlug = $baseTargetSlug;
        $n          = 2;
        while ($model->where('slug', $targetSlug)->where('locale', $targetLocale)->first() !== null) {
            $targetSlug = $baseTargetSlug . '-' . $n;
            $n++;
        }

        $sourceGroup = trim((string) ($src['translation_group'] ?? ''));
        $group       = $sourceGroup !== '' ? $sourceGroup : (string) $id;
        if ($sourceGroup === '') {
            $model->update($id, ['translation_group' => $group]);
        }

        if ($model->where('translation_group', $group)->where('locale', $targetLocale)->first() !== null) {
            return redirect()->to(site_url('admin/position-items'))
                ->with('error', 'Une variante existe déjà pour cette langue dans ce groupe.');
        }

        $titleBase = trim((string) ($src['title'] ?? 'Sans titre'));
        $suffix    = $targetLocale === 'en' ? ' (EN)' : ' (FR)';

        $newId = $model->insert([
            'slug'               => $targetSlug,
            'locale'             => $targetLocale,
            'translation_group'  => $group,
            'title'              => $titleBase . $suffix,
            'excerpt'            => $src['excerpt'] ?? null,
            'summary'            => $src['summary'] ?? null,
            'body'               => $src['body'] ?? null,
            'body_content_mode'  => $src['body_content_mode'] ?? 'blocks',
            'body_blocks'        => $src['body_blocks'] ?? null,
            'types_csv'          => $src['types_csv'] ?? '',
            'sectors_csv'        => $src['sectors_csv'] ?? '',
            'reading_minutes'    => $src['reading_minutes'] ?? null,
            'publication_state'  => PositionItemModel::PUBLICATION_DRAFT,
            'meta_title'         => $src['meta_title'] ?? null,
            'meta_description'   => $src['meta_description'] ?? null,
            'published_at'       => null,
        ]);

        return redirect()->to(site_url('admin/position-items/edit/' . (int) $newId))
            ->with('message', 'Brouillon créé pour la langue cible. Complétez la traduction puis publiez.');
    }

    /**
     * @return array<string, string>
     */
    private function rules(bool $isEdit): array
    {
        $pubList = implode(',', array_keys(PositionItemModel::publicationStateLabels()));
        $rules   = [
            'slug'              => 'required|max_length[160]',
            'title'             => 'required|max_length[255]',
            'excerpt'           => 'permit_empty',
            'summary'           => 'permit_empty',
            'body'              => 'permit_empty',
            'body_content_mode' => 'required|in_list[html,blocks]',
            'publication_state' => 'required|in_list[' . $pubList . ']',
            'reading_minutes'   => 'permit_empty|integer',
            'meta_title'        => 'permit_empty|max_length[255]',
            'meta_description'  => 'permit_empty|max_length[512]',
            'translation_group' => 'permit_empty|max_length[64]',
        ];
        if (! $isEdit) {
            $rules['locale'] = 'required|in_list[fr,en]';
        }

        return $rules;
    }

    private function normalizeLocale(string $raw): string
    {
        $s = strtolower(trim($raw));

        return in_array($s, ['fr', 'en'], true) ? $s : 'fr';
    }

    private function normalizeSlug(string $raw): string
    {
        $s = mb_strtolower(trim($raw), 'UTF-8');
        $s = preg_replace('/[^a-z0-9\-]+/u', '-', $s) ?? '';
        $s = preg_replace('/-+/', '-', $s) ?? '';

        return trim($s, '-');
    }

    private function nullableString(string $field): ?string
    {
        $v = trim((string) $this->request->getPost($field));

        return $v === '' ? null : $v;
    }

    private function nullableUInt(string $field): ?int
    {
        $v = trim((string) $this->request->getPost($field));
        if ($v === '') {
            return null;
        }

        return max(0, (int) $v);
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

        return implode(',', array_values(array_unique($out)));
    }

    private function typesCsvFromPost(): string
    {
        $raw = $this->request->getPost('types');
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

    /**
     * @param array{bodyContentMode: string, canUseAdvancedHtml: bool} $formData
     */
    private function positionFormScripts(array $formData): string
    {
        $scripts = '<script defer src="' . esc(base_url('js/admin/project-block-repeatable.js?v=6'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-blocks-form.js'), 'attr') . '"></script>';
        if ($formData['canUseAdvancedHtml']) {
            $scripts = $this->editorFormExtraScriptsForSelector('#pp-body') . $scripts;
        }

        return $scripts;
    }

    /**
     * @return array{
     *   item: array<string, mixed>|null,
     *   sectors: list<array<string, mixed>>,
     *   blocksForForm: list<array<string, mixed>>,
     *   bodyContentMode: string,
     *   canUseAdvancedHtml: bool,
     *   bodyStoredHtml: string,
     *   publicPreviewUrl: ?string
     * }
     */
    private function positionFormViewData(?array $item): array
    {
        helper('admin');

        $oldBlocks = old('blocks');
        if (is_array($oldBlocks)) {
            $blocksForForm = array_values($oldBlocks);
        } elseif ($item !== null) {
            $blocksForForm = ProjectBodyBlocksNormalizer::blocksForForm((string) ($item['body_blocks'] ?? ''));
        } else {
            $blocksForForm = [];
        }

        $existingMode = $item !== null ? strtolower(trim((string) ($item['body_content_mode'] ?? 'html'))) : 'blocks';
        if (! in_array($existingMode, ['html', 'blocks'], true)) {
            $existingMode = $item !== null ? 'html' : 'blocks';
        }

        $bodyStored   = $item !== null ? trim((string) ($item['body'] ?? '')) : '';
        $blocksStored = $item !== null ? trim((string) ($item['body_blocks'] ?? '')) : '';
        $hasBlocks    = $blocksStored !== '' && $blocksStored !== '[]';

        $defaultMode = $item === null ? 'blocks' : $existingMode;
        if ($item !== null && $bodyStored !== '' && ! $hasBlocks) {
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

        if ($item === null && $blocksForForm === [] && $bodyMode === 'blocks') {
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
        if ($item !== null
            && (string) ($item['publication_state'] ?? '') === PositionItemModel::PUBLICATION_PUBLISHED
        ) {
            helper('admin');
            $previewUrl = admin_public_position_url(
                (string) ($item['slug'] ?? ''),
                (string) ($item['locale'] ?? 'fr'),
            );
        }

        helper('admin');

        return [
            'item'                   => $item,
            'sectors'                => model(SectorModel::class)->listOrdered(),
            'blocksForForm'          => $blocksForForm,
            'bodyContentMode'        => $bodyMode,
            'canUseAdvancedHtml'     => $canUseAdvancedHtml,
            'bodyStoredHtml'         => $bodyStored,
            'publicPreviewUrl'       => $previewUrl,
            'translationPartnerNav'  => admin_translation_partner_nav(
                $item,
                PositionItemModel::class,
                'admin/position-items',
            ),
        ];
    }

    /**
     * @param array<string, mixed>|null $existing
     *
     * @return array{body: ?string, body_content_mode: string, body_blocks: ?string}|null
     */
    private function resolveBodyPayload(?array $existing): ?array
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

        $mode       = ProjectBodyBlocksNormalizer::contentMode($this->request);
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
