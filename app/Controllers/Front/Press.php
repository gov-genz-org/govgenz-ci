<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Libraries\CmsProgramListHero;
use App\Models\CmsPageModel;
use App\Models\CmsPostModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Press extends BaseController
{
    public function index()
    {
        helper(['cms', 'language']);

        $listPage = model(CmsPageModel::class)->getPublishedBySlug(cms_press_list_page_slug());
        $hero     = CmsProgramListHero::resolve(
            $listPage,
            lang('Site.breadcrumb_press'),
            lang('Site.press_index_title'),
        );
        if ($hero['heroOverline'] === '') {
            $hero['heroOverline'] = lang('Site.press_overline');
        }
        if ($hero['heroLead'] === '') {
            $hero['heroLead'] = lang('Site.press_index_intro');
        }

        $posts = model(CmsPostModel::class)->listPublishedNewestFirst();

        return view('front/layout', [
            'title'           => $hero['layoutTitle'],
            'metaDescription' => $hero['layoutMeta'],
            'main'            => view('front/press/index', [
                'posts'        => $posts,
                'heroOverline' => $hero['heroOverline'],
                'heroTitle'    => $hero['heroTitle'],
                'heroLead'     => $hero['heroLead'],
            ]),
            'navActive'       => 'press',
            'mainExtraClass'  => 'ggz-layout-full',
        ]);
    }

    public function show(string $slug)
    {
        helper(['cms']);
        $post = model(CmsPostModel::class)->getPublishedBySlug($slug);
        if ($post === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $title = $post['meta_title'] ?? $post['title'];

        return view('front/layout', [
            'title'           => $title,
            'metaDescription' => trim((string) ($post['meta_description'] ?? '')),
            'main'            => view('front/press/show', ['post' => $post]),
            'navActive'       => 'press',
            'mainExtraClass'  => 'ggz-layout-full',
        ]);
    }
}
