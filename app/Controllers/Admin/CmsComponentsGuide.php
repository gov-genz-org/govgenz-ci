<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CmsPublicHtmlGuide;

/**
 * Aide éditoriale : snippets HTML + aperçu charte (CSS public scopé à .cms-guide-preview-host).
 */
class CmsComponentsGuide extends BaseController
{
    public function index()
    {
        $cssBase = base_url('assets/css/');
        $extra   = <<<HTML
<link rel="stylesheet" href="{$cssBase}govgenz-fonts.css">
<link rel="stylesheet" href="{$cssBase}govgenz-components.css">
<link rel="stylesheet" href="{$cssBase}govgenz-template.css">
<link rel="stylesheet" href="{$cssBase}govgenz-cms-shell.css">
<link rel="stylesheet" href="{$cssBase}govgenz-cms-guide-front-scoped.css">
<link rel="stylesheet" href="{$cssBase}ggz-legal-page.css">
<link rel="stylesheet" href="{$cssBase}ggz-press-page.css">
<link rel="stylesheet" href="{$cssBase}govgenz-guide-preview-parity.css">
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
