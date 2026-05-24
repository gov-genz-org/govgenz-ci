<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Models\CmsPageModel;

class Home extends BaseController
{
    public function index()
    {
        helper(['cms']);

        $pages = model(CmsPageModel::class);
        $page  = $pages->getPublishedBySlug('home');

        if ($page === null) {
            return view('front/layout', [
                'title'           => 'GoV Gen Z Madagascar',
                'metaDescription' => '',
                'main'            => view('front/home_missing'),
                'navActive'       => 'home',
                'mainExtraClass'  => '',
            ]);
        }

        $title = trim((string) ($page['meta_title'] ?? ''));
        if ($title === '') {
            $title = (string) $page['title'];
        }

        return view('front/layout', [
            'title'           => $title,
            'metaDescription' => trim((string) ($page['meta_description'] ?? '')),
            'main'            => view('front/home', ['page' => $page]),
            'navActive'       => 'home',
            'mainExtraClass'  => 'ggz-layout-full',
        ]);
    }
}
