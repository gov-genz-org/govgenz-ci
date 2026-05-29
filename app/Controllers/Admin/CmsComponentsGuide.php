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
                'sections'    => CmsPublicHtmlGuide::sections(),
                'pageBlocks'  => [
                    ['id' => 'section_text', 'label' => 'Texte', 'render' => 'Section éditoriale (paragraphes + puces + source)'],
                    ['id' => 'cards_simple', 'label' => 'Cartes · Simple', 'render' => 'Grille de cartes génériques'],
                    ['id' => 'cards_circle', 'label' => 'Cartes · Cercle', 'render' => 'Bloc “Qui sommes-nous” avec valeurs/médias'],
                    ['id' => 'cards_pillar', 'label' => 'Cartes · ADN', 'render' => 'Cartes ADN (numéro, sur-titre, puces)'],
                    ['id' => 'cards_tile', 'label' => 'Cartes · Tuiles', 'render' => 'Tuiles cliquables (titre/sous-titre/lien)'],
                    ['id' => 'stats_grid', 'label' => 'Chiffres', 'render' => 'Statistiques + actions'],
                    ['id' => 'organization_hub', 'label' => 'Organisation', 'render' => 'Hub central + fonctions/équipes'],
                    ['id' => 'contact_grid', 'label' => 'Contacts', 'render' => 'Grille de blocs contact'],
                    ['id' => 'cta_panel', 'label' => 'Appel à action', 'render' => 'Bandeau CTA avec boutons'],
                    ['id' => 'legal_prose', 'label' => 'Mentions / texte long', 'render' => 'Sections longues structurées'],
                    ['id' => 'sources', 'label' => 'Sources', 'render' => 'Liste de références'],
                    ['id' => 'sectors_grid', 'label' => 'Grille secteurs', 'render' => 'Grille dynamique depuis la base secteurs'],
                    ['id' => 'html', 'label' => 'HTML libre', 'render' => 'Rendu brut (avancé, à éviter en modération courante)'],
                ],
            ]),
        ]);
    }
}
