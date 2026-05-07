<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Models\CmsPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Page extends BaseController
{
    private function renderSlug(string $slug, string $navActive): string
    {
        $pages = model(CmsPageModel::class);
        $page  = $pages->getPublishedBySlug($slug);
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Page introuvable.');
        }

        $title = $page['meta_title'] ?? $page['title'];

        helper('cms');

        return view('front/layout', [
            'title'           => $title,
            'metaDescription' => trim((string) ($page['meta_description'] ?? '')),
            'main'            => view('front/page', ['page' => $page]),
            'navActive'       => $navActive,
            'mainExtraClass'  => cms_layout_main_class($page['layout_key'] ?? null),
        ]);
    }

    public function contact()
    {
        return $this->renderSlug('contact', 'contact');
    }

    public function redirectLegacyAbout(): ResponseInterface
    {
        helper(['locale']);

        $slug = service('request')->getLocale() === 'en' ? 'who-we-are' : 'qui-sommes-nous';

        return redirect()->to(localized_site_url($slug), 301);
    }

    /**
     * Page CMS publique dont le slug correspond au premier segment d’URL (ex. /mentions-legales).
     * Les routes explicites (contact, press, join, admin…) sont déclarées avant dans Routes.php.
     */
    public function show(string $slug)
    {
        return $this->renderSlug($slug, '');
    }
}
