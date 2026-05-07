<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CmsPublicHtmlGuide;

/**
 * Aide éditoriale : snippets HTML + aperçu charte (styles isolés, sans page publique).
 */
class CmsComponentsGuide extends BaseController
{
    public function index()
    {
        $cssBase = base_url('assets/css/');
        $fonts   = 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Fraunces:ital,wght@0,400;0,600;0,800;0,900;1,400&family=JetBrains+Mono:wght@400;700&display=swap';
        $extra   = <<<HTML
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="{$fonts}" rel="stylesheet">
<link rel="stylesheet" href="{$cssBase}govgenz-tokens.css">
<link rel="stylesheet" href="{$cssBase}govgenz-template.css">
<link rel="stylesheet" href="{$cssBase}admin-cms-guide-preview.css">
HTML;

        return view('admin/layout', [
            'title'     => 'Aide — composants HTML',
            'extraHead' => $extra,
            'main'      => view('admin/cms_components_guide', [
                'sections' => CmsPublicHtmlGuide::sections(),
            ]),
        ]);
    }
}
