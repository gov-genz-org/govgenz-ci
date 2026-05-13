<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\SiteContext;
use App\Models\CmsPageModel;
use App\Models\SectorModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Sectors extends BaseController
{
    public function index()
    {
        helper(['cms', 'locale']);

        $slug = SiteContext::locale() === 'en' ? 'sectors' : 'secteurs';
        $page  = model(CmsPageModel::class)->getPublishedBySlug($slug);
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound('Page introuvable.');
        }

        $title = trim((string) ($page['meta_title'] ?? ''));
        if ($title === '') {
            $title = (string) ($page['title'] ?? 'Secteurs');
        }

        $sectors = model(SectorModel::class)->listOrdered();

        return view('front/layout', [
            'title'           => $title,
            'metaDescription' => trim((string) ($page['meta_description'] ?? '')),
            'main'            => view('front/sectors/index', [
                'page'    => $page,
                'sectors' => $sectors,
            ]),
            'navActive'       => '',
            'mainExtraClass'  => cms_layout_main_class($page['layout_key'] ?? null),
        ]);
    }
}
