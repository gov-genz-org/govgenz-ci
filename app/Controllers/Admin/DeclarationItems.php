<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\LocaleSlug;
use App\Libraries\ProjectBodyBlocksNormalizer;
use App\Models\DeclarationItemModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class DeclarationItems extends BaseController
{
    public function index()
    {
        $model = model(DeclarationItemModel::class);

        $loc = $this->request->getGet('loc');
        if (is_string($loc) && in_array($loc, ['fr', 'en'], true)) {
            $model = $model->where('locale', $loc);
        }

        $pub = $this->request->getGet('pub');
        if (is_string($pub) && in_array($pub, array_keys(DeclarationItemModel::publicationStateLabels()), true)) {
            $model = $model->where('publication_state', $pub);
        }

        $section = $this->request->getGet('section');
        if (is_string($section) && in_array($section, DeclarationItemModel::listSectionCodes(), true)) {
            $model = $model->where('list_section', $section);
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
                'list_section'      => 'list_section',
                'kind'              => 'kind',
                'sort_order'        => 'sort_order',
                'publication_state' => 'publication_state',
                'updated_at'        => 'updated_at',
            ],
            'sort_order',
            'asc',
            ['pub', 'q', 'loc', 'section'],
        );

        return view('admin/layout', [
            'title' => lang('Admin.title_declarations'),
            'main'  => view('admin/declaration_items/index', [
                'rows'                        => $list['rows'],
                'pager'                       => $list['pager'],
                'sort'                        => $list['sort'],
                'dir'                         => $list['dir'],
                'filterPub'                   => is_string($pub) && in_array($pub, array_keys(DeclarationItemModel::publicationStateLabels()), true) ? $pub : 'all',
                'filterLocale'                => is_string($loc) && in_array($loc, ['fr', 'en'], true) ? $loc : 'all',
                'filterSection'               => is_string($section) && in_array($section, DeclarationItemModel::listSectionCodes(), true) ? $section : 'all',
                'searchQuery'                 => $searchQuery,
                'pubLabels'                   => DeclarationItemModel::publicationStateLabels(),
                'sectionLabels'               => DeclarationItemModel::listSectionLabels(),
                'kindLabels'                  => DeclarationItemModel::kindLabels(),
                'translationLocalesByGroup' => $this->translationLocalesByGroupForRows($list['rows'], DeclarationItemModel::class),
            ]),
        ]);
    }

    public function create()
    {
        $formData = $this->formViewData(null);

        return view('admin/layout', [
            'title'        => lang('Admin.form_declaration_new'),
            'main'         => view('admin/declaration_items/form', $formData),
            'extraScripts' => $this->declarationFormScripts($formData),
        ]);
    }

    public function store(): ResponseInterface
    {
        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug   = LocaleSlug::normalizeSlug((string) $this->request->getPost('slug'));
        $locale = LocaleSlug::normalizeLocale((string) $this->request->getPost('locale'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        $model = model(DeclarationItemModel::class);
        if ($model->where('slug', $slug)->where('locale', $locale)->first() !== null) {
            return redirect()->back()->withInput()->with('error', lang('Admin.error_slug_locale_taken'));
        }

        $pubState    = (string) $this->request->getPost('publication_state');
        $publishedAt = $this->publishedAtFromPost($pubState, null);
        $tgIn        = trim((string) $this->request->getPost('translation_group'));

        $model->insert(array_merge(
            $this->payloadFromPost($slug, $locale, $tgIn, $pubState, $publishedAt),
            $this->resolveBodyPayload(null),
        ));

        $newId = (int) $model->getInsertID();
        if ($newId > 0 && $tgIn === '') {
            $model->update($newId, ['translation_group' => (string) $newId]);
        }

        return $this->adminRedirectToEdit('admin/declaration-items', $newId, lang('Admin.flash_declaration_created'));
    }

    public function edit(int $id): string
    {
        $item = model(DeclarationItemModel::class)->find($id);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $formData = $this->formViewData($item);

        return view('admin/layout', [
            'title'        => lang('Admin.form_declaration_edit'),
            'main'         => view('admin/declaration_items/form', $formData),
            'extraScripts' => $this->declarationFormScripts($formData),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(DeclarationItemModel::class);
        $item  = $model->find($id);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->rules(true))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug   = LocaleSlug::normalizeSlug((string) $this->request->getPost('slug'));
        $locale = LocaleSlug::normalizeLocale((string) ($item['locale'] ?? 'fr'));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('errors', ['slug' => 'Slug invalide.']);
        }

        if ($model->where('slug', $slug)->where('locale', $locale)->where('id !=', $id)->first() !== null) {
            return redirect()->back()->withInput()->with('error', lang('Admin.error_slug_locale_taken'));
        }

        $pubState    = (string) $this->request->getPost('publication_state');
        $publishedAt = $this->publishedAtFromPost($pubState, $item['published_at'] ?? null);

        $tgIn = trim((string) $this->request->getPost('translation_group'));
        if ($tgIn === '') {
            $tgIn = trim((string) ($item['translation_group'] ?? ''));
        }
        if ($tgIn === '') {
            $tgIn = (string) $id;
        }

        $model->update($id, array_merge(
            $this->payloadFromPost($slug, $locale, $tgIn, $pubState, $publishedAt),
            $this->resolveBodyPayload($item),
        ));

        return $this->adminRedirectToEdit('admin/declaration-items', $id, lang('Admin.flash_declaration_updated'));
    }

    public function delete(int $id): ResponseInterface
    {
        $model = model(DeclarationItemModel::class);
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete($id);

        return redirect()->to(site_url('admin/declaration-items'))->with('message', lang('Admin.flash_declaration_deleted'));
    }

    /**
     * @return array<string, string>
     */
    private function rules(bool $isEdit): array
    {
        $pubList     = implode(',', array_keys(DeclarationItemModel::publicationStateLabels()));
        $kindList    = implode(',', DeclarationItemModel::kindCodes());
        $sectionList = implode(',', DeclarationItemModel::listSectionCodes());
        $rules       = [
            'slug'              => 'required|max_length[160]',
            'title'             => 'required|max_length[255]',
            'summary'           => 'permit_empty',
            'body'              => 'permit_empty',
            'kind'              => 'required|in_list[' . $kindList . ']',
            'list_section'      => 'required|in_list[' . $sectionList . ']',
            'meta_line'         => 'permit_empty|max_length[255]',
            'band_label'        => 'permit_empty|max_length[120]',
            'badge_label'       => 'permit_empty|max_length[120]',
            'cta_label'         => 'permit_empty|max_length[120]',
            'cta_href'          => 'permit_empty|max_length[512]',
            'sort_order'        => 'permit_empty|integer',
            'publication_state' => 'required|in_list[' . $pubList . ']',
            'translation_group' => 'permit_empty|max_length[64]',
        ];
        if (! $isEdit) {
            $rules['locale'] = 'required|in_list[fr,en]';
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromPost(
        string $slug,
        string $locale,
        string $translationGroup,
        string $pubState,
        ?string $publishedAt,
    ): array {
        $sort = (int) $this->request->getPost('sort_order');

        return [
            'slug'              => $slug,
            'locale'            => $locale,
            'translation_group' => $translationGroup !== '' ? $translationGroup : null,
            'title'             => trim((string) $this->request->getPost('title')),
            'summary'           => $this->nullableString('summary'),
            'kind'              => strtolower(trim((string) $this->request->getPost('kind'))),
            'list_section'      => strtolower(trim((string) $this->request->getPost('list_section'))),
            'meta_line'         => $this->nullableString('meta_line'),
            'band_label'        => $this->nullableString('band_label'),
            'badge_label'       => $this->nullableString('badge_label'),
            'cta_label'         => $this->nullableString('cta_label'),
            'cta_href'          => $this->nullableString('cta_href'),
            'sort_order'        => max(0, $sort),
            'publication_state' => $pubState,
            'published_at'      => $publishedAt,
        ];
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function declarationFormScripts(array $formData): string
    {
        $scripts = '<script defer src="' . esc(base_url('js/admin/project-block-repeatable.js?v=6'), 'attr') . '"></script>'
            . '<script defer src="' . esc(base_url('js/admin/project-blocks-form.js'), 'attr') . '"></script>';
        if ($formData['canUseAdvancedHtml']) {
            $scripts = $this->editorFormExtraScriptsForSelector('#pp-body') . $scripts;
        }

        return $scripts;
    }

    /**
     * @param array<string, mixed>|null $item
     *
     * @return array<string, mixed>
     */
    private function formViewData(?array $item): array
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

        $listUrl = null;
        if ($item !== null && (string) ($item['publication_state'] ?? '') === DeclarationItemModel::PUBLICATION_PUBLISHED) {
            $slug = (string) ($item['slug'] ?? '');
            if ($slug !== '') {
                $listUrl = admin_public_declaration_item_url($slug, (string) ($item['locale'] ?? 'fr'));
            }
        }

        return [
            'item'                  => $item,
            'kindLabels'            => DeclarationItemModel::kindLabels(),
            'sectionLabels'         => DeclarationItemModel::listSectionLabels(),
            'pubLabels'             => DeclarationItemModel::publicationStateLabels(),
            'publicListUrl'         => $listUrl,
            'blocksForForm'         => $blocksForForm,
            'bodyContentMode'       => $bodyMode,
            'canUseAdvancedHtml'    => $canUseAdvancedHtml,
            'bodyStoredHtml'        => $bodyStored,
            'translationPartnerNav' => admin_translation_partner_nav(
                $item,
                DeclarationItemModel::class,
                'admin/declaration-items',
            ),
        ];
    }

    /**
     * @param array<string, mixed>|null $existing
     *
     * @return array{body: ?string, body_content_mode: string, body_blocks: ?string}
     */
    private function resolveBodyPayload(?array $existing): array
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

            return [
                'body'              => null,
                'body_content_mode' => 'blocks',
                'body_blocks'       => $blocksJson !== null && $blocksJson !== '' ? $blocksJson : null,
            ];
        }

        $mode       = ProjectBodyBlocksNormalizer::contentMode($this->request);
        $blocksJson = ProjectBodyBlocksNormalizer::bodyBlocksJson($this->request);
        if ($mode === 'blocks') {
            return [
                'body'              => null,
                'body_content_mode' => 'blocks',
                'body_blocks'       => $blocksJson !== null && $blocksJson !== '' && $blocksJson !== '[]' ? $blocksJson : null,
            ];
        }

        return [
            'body'              => $this->nullableString('body'),
            'body_content_mode' => 'html',
            'body_blocks'       => null,
        ];
    }

    private function nullableString(string $field): ?string
    {
        $v = trim((string) $this->request->getPost($field));

        return $v === '' ? null : $v;
    }

    private function publishedAtFromPost(string $pubState, mixed $existing): ?string
    {
        if ($pubState !== DeclarationItemModel::PUBLICATION_PUBLISHED) {
            return null;
        }

        $raw = trim((string) $this->request->getPost('published_at'));
        if ($raw !== '') {
            $ts = strtotime($raw);

            return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
        }

        if ($existing !== null && trim((string) $existing) !== '') {
            return (string) $existing;
        }

        return date('Y-m-d H:i:s');
    }
}
