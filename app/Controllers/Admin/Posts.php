<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\LocaleSlug;
use App\Models\CmsPostModel;
use CodeIgniter\HTTP\ResponseInterface;

class Posts extends BaseController
{
    public function index()
    {
        $status = $this->request->getGet('status');
        $filter = is_string($status) && in_array($status, ['draft', 'published'], true) ? $status : null;

        $model = model(CmsPostModel::class);
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
                'locale'       => 'locale',
                'slug'         => 'slug',
                'title'        => 'title',
                'status'       => 'status',
                'published_at' => 'published_at',
            ],
            'id',
            'desc',
            ['status', 'q'],
        );

        $translationLocalesByGroup = $this->translationLocalesByGroupForRows($list['rows'], CmsPostModel::class);

        return view('admin/layout', [
            'title' => 'Articles / presse',
            'main'  => view('admin/posts/index', [
                'posts'                       => $list['rows'],
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
            'title'         => 'Nouvel article',
            'main'          => view('admin/posts/form', ['post' => null]),
            'extraScripts' => $this->editorFormExtraScripts(),
        ]);
    }

    public function store(): ResponseInterface
    {
        $rules = $this->rules();
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug   = LocaleSlug::normalizeSlug($this->request->getPost('slug'));
        $locale = LocaleSlug::normalizeLocale($this->request->getPost('locale'));
        $model  = model(CmsPostModel::class);
        if ($model->where('slug', $slug)->where('locale', $locale)->first() !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug existe déjà pour cette langue.');
        }

        $status = $this->request->getPost('status');
        $publishedAt = $this->normalizePublishedAt(
            is_string($status) ? $status : null,
            $this->request->getPost('published_at'),
            null,
        );

        $tgrp = LocaleSlug::normalizeTranslationGroup($this->request->getPost('translation_group'));

        $model->insert([
            'slug'               => $slug,
            'locale'             => $locale,
            'translation_group'  => $tgrp,
            'title'              => $this->request->getPost('title'),
            'excerpt'            => $this->request->getPost('excerpt') ?: null,
            'body_html'          => (string) $this->request->getPost('body_html'),
            'status'             => $status,
            'published_at'       => $publishedAt,
            'meta_title'         => $this->request->getPost('meta_title') ?: null,
            'meta_description'   => $this->request->getPost('meta_description') ?: null,
        ]);

        $newId = (int) $model->getInsertID();
        if ($newId > 0 && $tgrp === null) {
            $model->update($newId, ['translation_group' => (string) $newId]);
        }

        return $this->adminRedirectToEdit('admin/posts', $newId, lang('Admin.flash_post_created'));
    }

    public function edit(int $id)
    {
        $model = model(CmsPostModel::class);
        $post  = $model->find($id);
        if ($post === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/layout', [
            'title'         => 'Éditer l’article',
            'main'          => view('admin/posts/form', ['post' => $post]),
            'extraScripts' => $this->editorFormExtraScripts(),
        ]);
    }

    public function update(int $id): ResponseInterface
    {
        $model = model(CmsPostModel::class);
        $existing = $model->find($id);
        if ($existing === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = $this->rules();
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug   = LocaleSlug::normalizeSlug($this->request->getPost('slug'));
        $locale = LocaleSlug::normalizeLocale($this->request->getPost('locale'));
        $other  = $model->where('slug', $slug)->where('locale', $locale)->where('id !=', $id)->first();
        if ($other !== null) {
            return redirect()->back()->withInput()->with('error', 'Ce slug existe déjà pour cette langue.');
        }

        $status = $this->request->getPost('status');
        $publishedAt = $this->normalizePublishedAt(
            is_string($status) ? $status : null,
            $this->request->getPost('published_at'),
            isset($existing['published_at']) ? (string) $existing['published_at'] : null,
        );

        $tgrpIn = trim((string) $this->request->getPost('translation_group'));
        $tgrp   = $tgrpIn !== '' ? $tgrpIn : (string) ($existing['translation_group'] ?? $id);

        $model->update($id, [
            'slug'               => $slug,
            'locale'             => $locale,
            'translation_group'  => $tgrp,
            'title'              => $this->request->getPost('title'),
            'excerpt'            => $this->request->getPost('excerpt') ?: null,
            'body_html'          => (string) $this->request->getPost('body_html'),
            'status'             => $status,
            'published_at'       => $publishedAt,
            'meta_title'         => $this->request->getPost('meta_title') ?: null,
            'meta_description'   => $this->request->getPost('meta_description') ?: null,
        ]);

        return $this->adminRedirectToEdit('admin/posts', $id, lang('Admin.flash_post_updated'));
    }

    public function duplicate(int $id): ResponseInterface
    {
        $model = model(CmsPostModel::class);
        $src   = $model->find($id);
        if ($src === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $srcLocale    = LocaleSlug::normalizeLocale($src['locale'] ?? 'fr');
        $targetLocale = $srcLocale === 'fr' ? 'en' : 'fr';
        $srcSlug = LocaleSlug::normalizeSlug((string) ($src['slug'] ?? ''));
        $baseTargetSlug = $srcSlug === ''
            ? 'article'
            : (string) preg_replace('/-en$/', '', $srcSlug);
        if ($baseTargetSlug === '') {
            $baseTargetSlug = 'article';
        }
        $targetSlug = $this->buildUniquePostSlugForLocale($baseTargetSlug, $targetLocale);

        $sourceGroup = trim((string) ($src['translation_group'] ?? ''));
        $group       = $sourceGroup !== '' ? $sourceGroup : (string) $id;
        if ($sourceGroup === '') {
            $model->update($id, ['translation_group' => $group]);
        }

        $partner = $model->where('translation_group', $group)->where('locale', $targetLocale)->first();
        if ($partner !== null) {
            return redirect()->to(site_url('admin/posts'))
                ->with('error', 'Une variante existe déjà pour cette langue dans ce groupe de traduction.');
        }

        $newTitle = trim((string) ($src['title'] ?? '')) . ($targetLocale === 'en' ? ' (EN)' : ' (FR)');

        $model->insert([
            'slug'               => $targetSlug,
            'locale'             => $targetLocale,
            'translation_group'  => $group,
            'title'              => $newTitle,
            'excerpt'            => $src['excerpt'] ?? null,
            'body_html'          => (string) ($src['body_html'] ?? ''),
            'status'             => 'draft',
            'published_at'       => null,
            'meta_title'         => $src['meta_title'] ?? null,
            'meta_description'   => $src['meta_description'] ?? null,
        ]);

        $newId = (int) $model->getInsertID();

        return redirect()
            ->to(site_url('admin/posts/edit/' . $newId))
            ->with('message', lang('Admin.flash_post_copy', [strtoupper($targetLocale)]));
    }

    public function delete(int $id): ResponseInterface
    {
        model(CmsPostModel::class)->delete($id);

        return redirect()->to(site_url('admin/posts'))->with('message', lang('Admin.flash_post_deleted'));
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
            'excerpt'          => 'permit_empty|max_length[512]',
            'body_html'        => 'permit_empty',
            'status'           => 'required|in_list[draft,published]',
            'published_at'     => 'permit_empty',
            'meta_title'       => 'permit_empty|max_length[255]',
            'meta_description' => 'permit_empty|max_length[512]',
        ];
    }

    private function normalizePublishedAt(?string $status, ?string $raw, ?string $fallback): ?string
    {
        if ($status !== 'published') {
            return null;
        }

        helper('admin');

        $raw = trim((string) ($raw ?: $fallback ?: ''));
        if ($raw === '') {
            $now = (new \DateTimeImmutable('now', new \DateTimeZone(admin_client_timezone())))->format('Y-m-d\TH:i:s');

            return admin_datetime_local_to_storage($now) ?? gmdate('Y-m-d H:i:s');
        }

        $stored = admin_datetime_local_to_storage($raw);
        if ($stored !== null) {
            return $stored;
        }

        $now = (new \DateTimeImmutable('now', new \DateTimeZone(admin_client_timezone())))->format('Y-m-d\TH:i:s');

        return admin_datetime_local_to_storage($now) ?? gmdate('Y-m-d H:i:s');
    }

    private function buildUniquePostSlugForLocale(string $baseSlug, string $locale): string
    {
        $slug = LocaleSlug::normalizeSlug($baseSlug);
        if ($slug === '') {
            $slug = 'article-' . $locale;
        }

        $candidate = $slug;
        $i         = 2;
        while (model(CmsPostModel::class)->where('slug', $candidate)->where('locale', $locale)->first() !== null) {
            $candidate = $slug . '-' . $i;
            $i++;
            if ($i > 500) {
                break;
            }
        }

        return $candidate;
    }
}
