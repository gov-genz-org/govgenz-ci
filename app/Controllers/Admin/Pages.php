<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CmsHeroPayload;
use App\Libraries\CmsPageBodyNormalizer;
use App\Models\CmsMediaModel;
use App\Models\CmsPageModel;
use CodeIgniter\HTTP\ResponseInterface;

class Pages extends BaseController
{
    public function index()
    {
        $status = $this->request->getGet('status');
        $filter = is_string($status) && in_array($status, ['draft', 'published'], true) ? $status : null;

        $model = model(CmsPageModel::class);
        if ($filter !== null) {
            $model = $model->where('status', $filter);
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
                'locale' => 'locale',
                'slug'   => 'slug',
                'title'  => 'title',
                'status' => 'status',
            ],
            'locale',
            'asc',
            ['status', 'q'],
            null,
            'slug',
            'ASC',
        );

        $translationLocalesByGroup = $this->translationLocalesByGroupForRows($list['rows'], CmsPageModel::class);

        return view('admin/layout', [
            'title' => 'Pages',
            'main'  => view('admin/pages/index', [
                'pages'                       => $list['rows'],
                'filterStatus'                => $filter ?? 'all',
                'searchQuery'                 => $searchQuery,
                'pager'                       => $list['pager'],
                'sort'                        => $list['sort'],
                'dir'                         => $list['dir'],
                'translationLocalesByGroup'   => $translationLocalesByGroup,
            ]),
        ]);
    }

    public function create()
    {
        return view('admin/layout', [
            'title'          => 'Nouvelle page',
            'main'           => view('admin/pages/form', array_merge(['page' => null], $this->pageFormViewExtras(null))),
            'extraScripts'   => $this->pagesEditorScripts(),
        ]);
    }

    public function store(): ResponseInterface
    {
        $rules = $this->rules();
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug   = $this->normalizedSlug($this->request->getPost('slug'));
        $locale = $this->normalizedLocale($this->request->getPost('locale'));
        $model  = model(CmsPageModel::class);
        if ($model->where('slug', $slug)->where('locale', $locale)->first() !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug existe déjà pour cette langue.');
        }

        helper('cms');

        $mode       = CmsPageBodyNormalizer::contentMode($this->request);
        $blocksJson = CmsPageBodyNormalizer::bodyBlocksJson($this->request);
        if ($mode === 'blocks' && ($blocksJson === null || $blocksJson === '' || $blocksJson === '[]')) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins une section valide (ex. avec un titre).');
        }

        $hero = CmsHeroPayload::fromPost($this->request);
        if ($hero['hero_image_id'] !== null && model(CmsMediaModel::class)->find($hero['hero_image_id']) === null) {
            return redirect()->back()->withInput()->with('error', 'Image hero : média introuvable (vérifiez l’identifiant dans la médiathèque).');
        }

        $tgrp = $this->normalizedTranslationGroup($this->request->getPost('translation_group'));

        $model->insert([
            'slug'               => $slug,
            'locale'             => $locale,
            'translation_group'  => $tgrp,
            'title'              => $this->request->getPost('title'),
            'body_html'          => $mode === 'blocks' ? '' : (string) $this->request->getPost('body_html'),
            'content_mode'       => $mode,
            'body_blocks'        => $mode === 'blocks' ? $blocksJson : null,
            'status'             => $this->request->getPost('status'),
            'meta_title'         => $this->request->getPost('meta_title') ?: null,
            'meta_description'   => $this->request->getPost('meta_description') ?: null,
            'layout_key'         => cms_layout_normalized($this->request->getPost('layout_key')),
            'hero_overline'      => $hero['hero_overline'],
            'hero_title'         => $hero['hero_title'],
            'hero_lead'          => $hero['hero_lead'],
            'hero_image_id'      => $hero['hero_image_id'],
            'hero_image_alt'     => $hero['hero_image_alt'],
        ]);

        $newId = (int) $model->getInsertID();
        if ($newId > 0 && $tgrp === null) {
            $model->update($newId, ['translation_group' => (string) $newId]);
        }

        return $this->adminRedirectToEdit('admin/pages', $newId, 'Page créée.');
    }

    public function edit(int $id)
    {
        $model = model(CmsPageModel::class);
        $page  = $model->find($id);
        if ($page === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/layout', [
            'title'          => 'Éditer la page',
            'main'           => view('admin/pages/form', array_merge(['page' => $page], $this->pageFormViewExtras($page))),
            'extraScripts'   => $this->pagesEditorScripts(),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(CmsPageModel::class);
        $existing = $model->find($id);
        if ($existing === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = $this->rules();
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug   = $this->normalizedSlug($this->request->getPost('slug'));
        $locale = $this->normalizedLocale($this->request->getPost('locale'));
        $other  = $model->where('slug', $slug)->where('locale', $locale)->where('id !=', $id)->first();
        if ($other !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug existe déjà pour cette langue.');
        }

        helper('cms');

        $mode       = CmsPageBodyNormalizer::contentMode($this->request);
        $blocksJson = CmsPageBodyNormalizer::bodyBlocksJson($this->request);
        if ($mode === 'blocks' && ($blocksJson === null || $blocksJson === '' || $blocksJson === '[]')) {
            return redirect()->back()->withInput()->with('error', 'Mode blocs : ajoutez au moins une section valide (ex. avec un titre).');
        }

        $hero = CmsHeroPayload::fromPost($this->request);
        if ($hero['hero_image_id'] !== null && model(CmsMediaModel::class)->find($hero['hero_image_id']) === null) {
            return redirect()->back()->withInput()->with('error', 'Image hero : média introuvable (vérifiez l’identifiant dans la médiathèque).');
        }

        $tgrpIn = trim((string) $this->request->getPost('translation_group'));
        $tgrp   = $tgrpIn !== '' ? $tgrpIn : (string) ($existing['translation_group'] ?? $id);

        $model->update($id, [
            'slug'               => $slug,
            'locale'             => $locale,
            'translation_group'  => $tgrp,
            'title'              => $this->request->getPost('title'),
            'body_html'          => $mode === 'blocks' ? '' : (string) $this->request->getPost('body_html'),
            'content_mode'       => $mode,
            'body_blocks'        => $mode === 'blocks' ? $blocksJson : null,
            'status'             => $this->request->getPost('status'),
            'meta_title'         => $this->request->getPost('meta_title') ?: null,
            'meta_description'   => $this->request->getPost('meta_description') ?: null,
            'layout_key'         => cms_layout_normalized($this->request->getPost('layout_key')),
            'hero_overline'      => $hero['hero_overline'],
            'hero_title'         => $hero['hero_title'],
            'hero_lead'          => $hero['hero_lead'],
            'hero_image_id'      => $hero['hero_image_id'],
            'hero_image_alt'     => $hero['hero_image_alt'],
        ]);

        return $this->adminRedirectToEdit('admin/pages', $id, 'Page mise à jour.');
    }

    public function delete(int $id): ResponseInterface
    {
        model(CmsPageModel::class)->delete($id);

        return redirect()->to(site_url('admin/pages'))->with('message', 'Page supprimée.');
    }

    public function duplicate(int $id): ResponseInterface
    {
        $model = model(CmsPageModel::class);
        $src   = $model->find($id);
        if ($src === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $srcLocale    = $this->normalizedLocale($src['locale'] ?? 'fr');
        $targetLocale = $srcLocale === 'fr' ? 'en' : 'fr';
        $srcSlug      = $this->normalizedSlug($src['slug'] ?? '');

        if ($srcSlug === 'home') {
            $baseTargetSlug = 'home';
        } else {
            $baseTargetSlug = $srcSlug === ''
                ? 'page'
                : (string) preg_replace('/-en$/', '', $srcSlug);
            if ($baseTargetSlug === '') {
                $baseTargetSlug = 'page';
            }
        }
        $targetSlug = $this->buildUniqueSlugForLocale($baseTargetSlug, $targetLocale);

        $sourceGroup = trim((string) ($src['translation_group'] ?? ''));
        $group       = $sourceGroup !== '' ? $sourceGroup : (string) $id;
        if ($sourceGroup === '') {
            $model->update($id, ['translation_group' => $group]);
        }

        $partner = $model->where('translation_group', $group)->where('locale', $targetLocale)->first();
        if ($partner !== null) {
            return redirect()->to(site_url('admin/pages'))
                ->with('error', 'Une variante existe déjà pour cette langue dans ce groupe de traduction.');
        }

        $newTitle = trim((string) ($src['title'] ?? '')) . ($targetLocale === 'en' ? ' (EN)' : ' (FR)');

        $model->insert([
            'slug'              => $targetSlug,
            'locale'            => $targetLocale,
            'translation_group' => $group,
            'title'             => $newTitle,
            'body_html'         => (string) ($src['body_html'] ?? ''),
            'content_mode'      => (string) ($src['content_mode'] ?? 'html'),
            'body_blocks'       => $src['body_blocks'] ?? null,
            'status'            => 'draft',
            'meta_title'        => $src['meta_title'] ?? null,
            'meta_description'  => $src['meta_description'] ?? null,
            'layout_key'        => $src['layout_key'] ?? null,
            'hero_overline'     => $src['hero_overline'] ?? null,
            'hero_title'        => $src['hero_title'] ?? null,
            'hero_lead'         => $src['hero_lead'] ?? null,
            'hero_image_id'     => $src['hero_image_id'] ?? null,
            'hero_image_alt'    => $src['hero_image_alt'] ?? null,
        ]);

        $newId = (int) $model->getInsertID();

        return redirect()
            ->to(site_url('admin/pages/edit/' . $newId))
            ->with('message', 'Copie créée en ' . strtoupper($targetLocale) . ' (brouillon).');
    }

    /**
     * @return array<string, string>
     */
    private function rules(): array
    {
        return [
            'slug'               => 'required|regex_match[/^[a-z0-9\-]+$/]|max_length[190]',
            'locale'             => 'required|in_list[fr,en]',
            'translation_group'  => 'permit_empty|max_length[64]',
            'title'              => 'required|max_length[255]',
            'content_mode'       => 'required|in_list[html,blocks]',
            'body_html'        => 'permit_empty',
            'status'           => 'required|in_list[draft,published]',
            'meta_title'       => 'permit_empty|max_length[255]',
            'meta_description' => 'permit_empty|max_length[512]',
            'layout_key'       => 'permit_empty|max_length[64]',
            'hero_overline'    => 'permit_empty|max_length[255]',
            'hero_title'       => 'permit_empty|max_length[255]',
            'hero_lead'        => 'permit_empty|max_length[65535]',
            'hero_image_id'    => 'permit_empty|integer',
            'hero_image_alt'   => 'permit_empty|max_length[255]',
        ];
    }

    /**
     * @return array{contentMode: string, blocksForForm: list<array<string, mixed>>}
     */
    private function pageFormViewExtras(?array $page): array
    {
        helper(['cms']);

        $contentMode = old('content_mode', $page !== null ? ($page['content_mode'] ?? 'html') : 'html');
        if (! in_array($contentMode, ['html', 'blocks'], true)) {
            $contentMode = 'html';
        }

        $blocksOld = old('blocks');
        if (is_array($blocksOld)) {
            return [
                'contentMode'     => $contentMode,
                'blocksForForm'   => array_values($blocksOld),
            ];
        }

        if ($page !== null && ($page['content_mode'] ?? '') === 'blocks' && ! empty($page['body_blocks'])) {
            $decoded = json_decode((string) $page['body_blocks'], true);
            if (is_array($decoded) && $decoded !== []) {
                return [
                    'contentMode'   => $contentMode,
                    'blocksForForm' => array_values($decoded),
                ];
            }
        }

        return [
            'contentMode'   => $contentMode,
            'blocksForForm' => [],
        ];
    }

    private function pagesEditorScripts(): string
    {
        return $this->editorFormExtraScripts()
            . '<script defer src="' . esc(base_url('js/admin/cms-blocks-form.js'), 'attr') . '"></script>';
    }

    private function normalizedSlug(?string $slug): string
    {
        return strtolower(trim((string) $slug));
    }

    private function normalizedLocale(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));

        return in_array($locale, ['fr', 'en'], true) ? $locale : 'fr';
    }

    private function normalizedTranslationGroup($raw): ?string
    {
        $g = trim((string) $raw);

        return $g !== '' ? $g : null;
    }

    private function buildUniqueSlugForLocale(string $baseSlug, string $locale): string
    {
        $slug = $this->normalizedSlug($baseSlug);
        if ($slug === '') {
            $slug = 'page-' . $locale;
        }

        $candidate = $slug;
        $i = 2;
        while (model(CmsPageModel::class)->where('slug', $candidate)->where('locale', $locale)->first() !== null) {
            $candidate = $slug . '-' . $i;
            $i++;
            if ($i > 500) {
                break;
            }
        }

        return $candidate;
    }
}
