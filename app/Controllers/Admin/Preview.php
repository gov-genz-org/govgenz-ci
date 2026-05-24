<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AdminRecordPreview;
use App\Libraries\CmsHeroPayload;
use App\Libraries\LocaleSlug;
use App\Libraries\CmsPageBodyNormalizer;
use App\Libraries\PublicNav;
use App\Libraries\SiteContext;
use App\Models\CmsPageModel;
use App\Models\CmsPostModel;
use App\Models\PositionItemModel;
use App\Models\ProjectProjectModel;
use App\Models\SiteNavItemModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Rendu « comme le site public » pour brouillons (session admin requise).
 */
class Preview extends BaseController
{
    /**
     * @param array<string, mixed>|null $row Page ou article avec champ locale optionnel.
     */
    private function warmPreviewSiteContext(?array $row): void
    {
        helper(['url', 'locale', 'cms']);

        $locale = (($row['locale'] ?? 'fr') === 'en') ? 'en' : 'fr';

        SiteContext::setLocale($locale);
        service('request')->setLocale($locale);

        $rows = model(SiteNavItemModel::class)->listActiveOrdered($locale);
        $links = [];
        foreach ($rows as $r) {
            $links[] = [
                'href'      => PublicNav::hrefFromRow($r),
                'label'     => (string) ($r['label'] ?? ''),
                'match_key' => (string) ($r['match_key'] ?? ''),
                'css_class' => trim((string) ($r['css_class'] ?? '')),
            ];
        }
        SiteContext::setNavMainLinks($links);
    }

    public function page(int $id)
    {
        $page = model(CmsPageModel::class)->find($id);
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->warmPreviewSiteContext($page);

        $title      = '(Brouillon) ' . ($page['title'] ?? 'Page');
        $ribbonText = 'Brouillon — aperçu de la dernière version enregistrée (pas les changements non sauvegardés dans ce formulaire).';

        $slug = (string) ($page['slug'] ?? '');
        $main = $slug === 'home'
            ? view('front/home', ['page' => $page])
            : view('front/page', ['page' => $page]);

        return view('front/layout', [
            'title'           => $title,
            'metaDescription' => trim((string) ($page['meta_description'] ?? '')),
            'main'            => $main,
            'navActive'       => $slug === 'home' ? 'home' : '',
            'mainExtraClass'  => $slug === 'home' ? 'ggz-layout-full' : cms_layout_main_class($page['layout_key'] ?? null),
            'previewRibbon'   => $ribbonText,
        ]);
    }

    /**
     * Aperçu avec les valeurs du formulaire en cours (sans INSERT/UPDATE en base).
     */
    public function pageDraft(int $id)
    {
        helper(['cms']);

        $page = model(CmsPageModel::class)->find($id);
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mergedLocale = strtolower(trim((string) $this->request->getPost('locale')));
        if (! in_array($mergedLocale, ['fr', 'en'], true)) {
            $mergedLocale = (string) ($page['locale'] ?? 'fr');
        }

        $this->warmPreviewSiteContext(['locale' => $mergedLocale]);

        $mode       = CmsPageBodyNormalizer::contentMode($this->request);
        $blocksJson = CmsPageBodyNormalizer::bodyBlocksJson($this->request);
        $hero       = CmsHeroPayload::fromPost($this->request);
        if ($hero['hero_image_id'] !== null && model(\App\Models\CmsMediaModel::class)->find($hero['hero_image_id']) === null) {
            $hero['hero_image_id'] = null;
        }

        $slugPost = strtolower(trim((string) $this->request->getPost('slug')));
        $merged   = array_merge($page, [
            'slug'               => $slugPost !== '' ? $slugPost : ($page['slug'] ?? ''),
            'locale'             => $mergedLocale,
            'title'              => (string) ($this->request->getPost('title') ?: ($page['title'] ?? '')),
            'meta_title'         => $this->request->getPost('meta_title') ?: ($page['meta_title'] ?? null),
            'meta_description'   => $this->request->getPost('meta_description') ?: ($page['meta_description'] ?? null),
            'layout_key'         => cms_layout_normalized($this->request->getPost('layout_key')),
            'content_mode'       => $mode,
            'body_blocks'        => $mode === 'blocks' ? $blocksJson : null,
            'body_html'          => $mode === 'blocks' ? '' : (string) $this->request->getPost('body_html'),
            'hero_overline'      => $hero['hero_overline'],
            'hero_title'         => $hero['hero_title'],
            'hero_lead'          => $hero['hero_lead'],
            'hero_image_id'      => $hero['hero_image_id'],
            'hero_image_alt'     => $hero['hero_image_alt'],
        ]);

        $title      = '(Prévisualisation) ' . ($merged['title'] ?? 'Page');
        $ribbonText = 'Formulaire non enregistré — cet onglet reflète uniquement ce que vous voyez dans l’éditeur. Cliquez sur « Enregistrer » pour garder ces changements.';

        $slug = (string) ($merged['slug'] ?? '');
        $main = $slug === 'home'
            ? view('front/home', ['page' => $merged])
            : view('front/page', ['page' => $merged]);

        return view('front/layout', [
            'title'           => $title,
            'metaDescription' => trim((string) ($merged['meta_description'] ?? '')),
            'main'            => $main,
            'navActive'       => $slug === 'home' ? 'home' : '',
            'mainExtraClass'  => $slug === 'home' ? 'ggz-layout-full' : cms_layout_main_class($merged['layout_key'] ?? null),
            'previewRibbon'   => $ribbonText,
        ]);
    }

    public function post(int $id)
    {
        $post = model(CmsPostModel::class)->find($id);
        if ($post === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->warmPreviewSiteContext($post);

        $title      = '(Brouillon) ' . ($post['title'] ?? 'Article');
        $ribbonText = lang('Admin.ribbon_record_preview_saved');

        return view('front/layout', [
            'title'         => $title,
            'main'          => view('front/press/show', ['post' => $post]),
            'navActive'     => 'press',
            'previewRibbon' => $ribbonText,
        ]);
    }

    public function postDraft(int $id)
    {
        helper('admin');

        $post = model(CmsPostModel::class)->find($id);
        if ($post === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mergedLocale = strtolower(trim((string) $this->request->getPost('locale')));
        if (! in_array($mergedLocale, ['fr', 'en'], true)) {
            $mergedLocale = (string) ($post['locale'] ?? 'fr');
        }

        $slugPost = LocaleSlug::normalizeSlug((string) $this->request->getPost('slug'));
        if ($slugPost === '') {
            $slugPost = (string) ($post['slug'] ?? '');
        }

        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['draft', 'published'], true)) {
            $status = (string) ($post['status'] ?? 'draft');
        }

        $merged = array_merge($post, [
            'slug'             => $slugPost,
            'locale'           => $mergedLocale,
            'title'            => trim((string) $this->request->getPost('title')),
            'excerpt'          => $this->request->getPost('excerpt') ?: null,
            'body_html'        => (string) $this->request->getPost('body_html'),
            'status'           => $status,
            'meta_title'       => $this->request->getPost('meta_title') ?: null,
            'meta_description' => $this->request->getPost('meta_description') ?: null,
        ]);

        $this->warmPreviewSiteContext($merged);

        $title = lang('Admin.preview_title_prefix') . ($merged['title'] ?? 'Article');

        return view('front/layout', [
            'title'         => $title,
            'main'          => view('front/press/show', ['post' => $merged]),
            'navActive'     => 'press',
            'previewRibbon' => lang('Admin.ribbon_record_preview_draft'),
        ]);
    }

    public function position(int $id)
    {
        $item = model(PositionItemModel::class)->find($id);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->warmPreviewSiteContext($item);

        return AdminRecordPreview::renderPosition($item, lang('Admin.ribbon_record_preview_saved'));
    }

    public function positionDraft(int $id)
    {
        $item = model(PositionItemModel::class)->find($id);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $merged = AdminRecordPreview::mergePositionFromPost($item, $this->request);
        if ($merged === null) {
            return redirect()->back()->with('error', lang('Admin.error_blocks_mode_empty'));
        }

        $this->warmPreviewSiteContext($merged);

        return AdminRecordPreview::renderPosition($merged, lang('Admin.ribbon_record_preview_draft'));
    }

    public function project(int $id)
    {
        $project = model(ProjectProjectModel::class)->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->warmPreviewSiteContext($project);

        return AdminRecordPreview::renderProject($project, lang('Admin.ribbon_record_preview_saved'));
    }

    public function projectDraft(int $id)
    {
        $project = model(ProjectProjectModel::class)->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $merged = AdminRecordPreview::mergeProjectFromPost($project, $this->request);
        if ($merged === null) {
            return redirect()->back()->with('error', lang('Admin.error_blocks_mode_empty'));
        }

        $this->warmPreviewSiteContext($merged);

        return AdminRecordPreview::renderProject($merged, lang('Admin.ribbon_record_preview_draft'));
    }
}
