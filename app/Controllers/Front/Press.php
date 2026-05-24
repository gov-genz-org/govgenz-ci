<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Models\CmsPostModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Press extends BaseController
{
    public function index()
    {
        helper(['cms']);
        $posts = model(CmsPostModel::class)->listPublishedNewestFirst();

        return view('front/layout', [
            'title'           => lang('Site.press_index_title'),
            'main'            => view('front/press/index', ['posts' => $posts]),
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
