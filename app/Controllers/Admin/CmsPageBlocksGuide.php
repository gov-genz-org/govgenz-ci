<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CmsBodyBlocksRenderer;

/**
 * Guide back-office des blocs Pages (Page Builder) avec exemples de rendu.
 */
class CmsPageBlocksGuide extends BaseController
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
            'title'     => 'Aide — blocs Pages',
            'extraHead' => $extra,
            'main'      => view('admin/cms_page_blocks_guide', [
                'examples' => $this->examples(),
            ]),
        ]);
    }

    /**
     * @return list<array{id:string,title:string,usage:string,blocks:list<array<string,mixed>>}>
     */
    private function examples(): array
    {
        return [
            [
                'id'    => 'section_text',
                'title' => 'Texte',
                'usage' => 'Section editoriale avec paragraphes et puces.',
                'blocks' => [[
                    'type' => 'section_text',
                    'paragraphs' => ['Premier paragraphe de demonstration.', 'Deuxieme paragraphe de demonstration.'],
                    'bullets' => ['Point 1', 'Point 2'],
                    'source' => 'Source: exemple',
                ]],
            ],
            [
                'id'    => 'cards_simple',
                'title' => 'Cartes simples',
                'usage' => 'Cartes generiques (titre, sous-titre, texte, lien).',
                'blocks' => [[
                    'type' => 'cards_grid',
                    'variant' => 'simple_cards',
                    'cards' => [
                        ['eyebrow' => 'Focus', 'title' => 'Education', 'subtitle' => 'Formation et recherche', 'description' => 'Description courte.', 'href' => '/contact'],
                        ['eyebrow' => 'Focus', 'title' => 'Sante', 'subtitle' => 'Acces et qualite', 'description' => 'Description courte.'],
                    ],
                ]],
            ],
            [
                'id'    => 'cards_circle',
                'title' => 'Cartes cercles',
                'usage' => 'Style Qui sommes-nous avec valeurs et sous-textes.',
                'blocks' => [[
                    'type' => 'cards_grid',
                    'variant' => 'circle_cards',
                    'cards' => [
                        ['value' => '12,44', 'unit' => 'M', 'title' => 'Enfants', 'subtitle' => '0-17 ans', 'description' => 'Description'],
                        ['value' => '8,68', 'unit' => 'M', 'title' => 'Jeunesse', 'subtitle' => '14-30 ans', 'description' => 'Description'],
                    ],
                ]],
            ],
            [
                'id'    => 'cards_pillar',
                'title' => 'Cartes ADN',
                'usage' => 'Piliers ADN avec numero, sur-titre et puces.',
                'blocks' => [[
                    'type' => 'cards_grid',
                    'variant' => 'pillar_cards',
                    'cards' => [
                        ['value' => '01', 'eyebrow' => 'POUR QUI', 'title' => 'Notre raison d etre', 'bullets' => ['Point A', 'Point B']],
                        ['value' => '02', 'eyebrow' => 'CE QUI NOUS GUIDE', 'title' => 'Nos valeurs', 'bullets' => ['Point A', 'Point B']],
                    ],
                ]],
            ],
            [
                'id'    => 'organization_hub',
                'title' => 'Organisation',
                'usage' => 'Noyau central + grille des fonctions.',
                'blocks' => [[
                    'type' => 'organization_hub',
                    'core_label' => 'NOYAU EXECUTIF CENTRAL',
                    'core_subtitle' => 'Coordination · Securite · Vision',
                    'core_href' => 'mailto:contact@govgenz.org',
                    'items' => [
                        ['name' => 'COORDINATION', 'subtitle' => 'Executifs · Regions', 'href' => 'mailto:coordination@govgenz.org'],
                        ['name' => 'COMMUNICATION', 'subtitle' => 'Presse · Contenus', 'href' => 'mailto:communication@govgenz.org'],
                    ],
                ]],
            ],
            [
                'id'    => 'contact_grid',
                'title' => 'Contacts',
                'usage' => 'Grille de contacts avec lien ou email.',
                'blocks' => [[
                    'type' => 'contact_grid',
                    'items' => [
                        ['label' => 'CONTACT GENERAL', 'title' => 'contact@govgenz.org', 'subtitle' => 'Premier contact', 'href' => 'mailto:contact@govgenz.org'],
                        ['label' => 'REJOINDRE', 'title' => 'Formulaire', 'subtitle' => 'Devenir membre', 'href' => '/join'],
                    ],
                ]],
            ],
            [
                'id'    => 'stats_grid',
                'title' => 'Chiffres',
                'usage' => 'Statistiques cles avec actions.',
                'blocks' => [[
                    'type' => 'stats_grid',
                    'stats' => [
                        ['value' => '72,6', 'suffix' => '%', 'label' => 'de la population a 0-30 ans'],
                        ['value' => '75,2', 'suffix' => '%', 'label' => 'de pauvrete nationale'],
                    ],
                    'actions' => [
                        ['label' => 'Demander l etude', 'href' => '/contact', 'variant' => 'secondary'],
                    ],
                ]],
            ],
            [
                'id'    => 'cta_panel',
                'title' => 'Appel a action',
                'usage' => 'Bandeau de texte + boutons.',
                'blocks' => [[
                    'type' => 'cta_panel',
                    'text' => 'Un texte court pour orienter vers une action.',
                    'actions' => [
                        ['label' => 'Nous ecrire', 'href' => '/contact', 'variant' => 'primary'],
                    ],
                ]],
            ],
            [
                'id'    => 'legal_prose',
                'title' => 'Mentions / texte long',
                'usage' => 'Sections longues structurees.',
                'blocks' => [[
                    'type' => 'legal_prose',
                    'sections' => [
                        ['heading' => 'Editeur', 'body' => "Texte de demonstration.\nDeuxieme paragraphe."],
                    ],
                ]],
            ],
            [
                'id'    => 'sources',
                'title' => 'Sources',
                'usage' => 'Liste de references.',
                'blocks' => [[
                    'type' => 'sources',
                    'lines' => ['Source 1', 'Source 2'],
                ]],
            ],
            [
                'id'    => 'sectors_grid',
                'title' => 'Grille secteurs',
                'usage' => 'Affichage automatique des secteurs depuis la base.',
                'blocks' => [[
                    'type' => 'sectors_grid',
                ]],
            ],
        ];
    }
}

