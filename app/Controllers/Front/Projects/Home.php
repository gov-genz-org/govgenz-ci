<?php

declare(strict_types=1);

namespace App\Controllers\Front\Projects;

use App\Controllers\BaseController;
use App\Libraries\SiteContext;

/**
 * Front module « projects » — développement sous /projects (mono-domaine).
 * Plus tard : même contrôleurs possibles derrière le sous-domaine (contexte SiteContext).
 */
class Home extends BaseController
{
    public function index()
    {
        return view('front/layout', [
            'title'           => 'Projets — GoV Gen Z',
            'metaDescription' => 'Module projets (développement).',
            'main'            => view('front/projects/home', [
                'segments' => SiteContext::publicUriSegments(),
            ]),
            'navActive'       => 'projects',
            'mainExtraClass'  => '',
        ]);
    }

    /**
     * Chemin interne après /projects/ (un ou plusieurs segments).
     */
    public function tail(string $path)
    {
        $path = trim($path, '/');
        $head = $path === '' ? '' : explode('/', $path, 2)[0];

        return view('front/layout', [
            'title'           => 'Projets — ' . ($head !== '' ? $head : 'GoV Gen Z'),
            'metaDescription' => '',
            'main'            => view('front/projects/tail', [
                'path'     => $path,
                'segments' => SiteContext::publicUriSegments(),
            ]),
            'navActive'       => 'projects',
            'mainExtraClass'  => '',
        ]);
    }
}
