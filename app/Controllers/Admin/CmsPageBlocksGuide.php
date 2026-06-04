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
<link rel="stylesheet" href="{$cssBase}sectors-grid-layouts.css">
<link rel="stylesheet" href="{$cssBase}structures-grid-layouts.css">
<link rel="stylesheet" href="{$cssBase}admin-cms-blocks-guide.css">
HTML;

        return view('admin/layout', [
            'title'     => lang('Admin.title_cms_blocks_guide'),
            'extraHead' => $extra,
            'main'      => view('admin/cms_page_blocks_guide', [
                'examples'              => $this->examples(),
                'declarationItemsUrl'   => site_url('admin/declaration-items'),
                'pagesIndexUrl'         => site_url('admin/pages'),
                'structuresAdminUrl'    => site_url('admin/structures'),
                'sectorsAdminUrl'       => site_url('admin/sectors'),
                'componentsGuideUrl'  => site_url('admin/cms-guide'),
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
                'usage' => 'Piliers ADN (page Declaration) : sur-titre, titre, puces — variante pillar_cards.',
                'blocks' => [[
                    'type' => 'cards_grid',
                    'variant' => 'pillar_cards',
                    'cards' => [
                        ['eyebrow' => 'Pour qui', 'title' => 'Notre raison d etre', 'bullets' => ['55 % de jeunes', '12 a 35 ans']],
                        ['eyebrow' => 'Ce qui nous guide', 'title' => 'Nos valeurs', 'bullets' => ['Integrite', 'Rigueur']],
                    ],
                ]],
            ],
            [
                'id'    => 'structures_grid_hub',
                'title' => 'Grille structures (hub)',
                'usage' => 'Page /structure (slug CMS structure) : noyau + fonctions (layout hub). Donnees : table Structures. Remplace organization_hub et le HTML statique GG_CMS_STRUCTURES_HUB.',
                'blocks' => [[
                    'type'            => 'structures_grid',
                    'layout'          => 'hub',
                    'kicker'          => 'Organigramme',
                    'title'           => 'Structure organisationnelle',
                    'lead'            => 'Noyau central et equipes specialisees.',
                    'banner_title'    => 'Structure — GoV Gen Z Madagascar',
                    'banner_subtitle' => 'Programme Paikady Taninjanaka',
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
                'title' => 'Mentions / texte long (prose)',
                'usage' => 'Sections longues en prose continue (mentions, CGU).',
                'blocks' => [[
                    'type'         => 'legal_prose',
                    'presentation' => 'prose',
                    'sections'     => [
                        ['heading' => 'Editeur', 'body' => "Texte de demonstration.\nDeuxieme paragraphe."],
                    ],
                ]],
            ],
            [
                'id'    => 'legal_prose_accordion',
                'title' => 'Mentions / accordéon',
                'usage' => 'Page Déclaration : engagements éthiques (presentation accordion).',
                'blocks' => [[
                    'type'         => 'legal_prose',
                    'presentation' => 'accordion',
                    'sections'     => [
                        [
                            'heading' => 'Charte ethique',
                            'body'    => 'Exemple de corps avec puces :',
                            'bullets' => ['Professionnalisme', 'Sources verifiables'],
                        ],
                        [
                            'heading' => 'Transparence financiere',
                            'body'    => 'Deuxieme section repliable.',
                            'bullets' => ['Rapports publies', 'Audit independant'],
                        ],
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
                'id'    => 'footer_columns',
                'title' => 'Colonnes pied de page',
                'usage' => 'Uniquement sur la page publiée slug site-footer (FR + EN). Remplace les trois colonnes sous le logo ; type technique footer_columns. Bouton éditeur : « + Colonnes pied de page ».',
                'blocks' => [[
                    'type' => 'footer_columns',
                    'columns' => [
                        [
                            'title' => 'Le mouvement',
                            'links' => [
                                ['label' => 'Qui sommes-nous', 'href' => '/qui-sommes-nous', 'soon' => 0],
                                ['label' => 'Contact', 'href' => '/contact', 'soon' => 0],
                            ],
                        ],
                        [
                            'title' => 'A venir',
                            'links' => [
                                ['label' => 'declaration.govgenz.org', 'href' => '', 'soon' => 1],
                            ],
                        ],
                        [
                            'title' => 'Contacts',
                            'links' => [
                                ['label' => 'contact@govgenz.org', 'href' => 'mailto:contact@govgenz.org', 'soon' => 0],
                            ],
                        ],
                    ],
                ]],
            ],
            [
                'id'    => 'sectors_grid',
                'title' => 'Grille secteurs',
                'usage' => 'Tuiles depuis la BDD — style Déclaration avec intro et bandeau teal facultatifs.',
                'blocks' => [[
                    'type'            => 'sectors_grid',
                    'layout'          => 'compact',
                    'kicker'          => 'Équipes de terrain',
                    'title'           => '14 Équipes sectorielles',
                    'lead'            => 'Chaque secteur clé de Madagascar est couvert par une équipe dédiée — bâtir, innover, servir le peuple.',
                    'banner_title'    => 'Équipes sectorielles — GoV Gen Z Madagascar',
                    'banner_subtitle' => '14 secteurs · Madagascar',
                ]],
            ],
            [
                'id'    => 'sectors_grid_wide',
                'title' => 'Grille secteurs — 7 colonnes',
                'usage' => 'Même tuiles vertes, grille large type page /secteurs.',
                'blocks' => [[
                    'type'   => 'sectors_grid',
                    'layout' => 'wide',
                ]],
            ],
            [
                'id'    => 'structures_grid',
                'title' => 'Grille structures (départements)',
                'usage' => 'Page Déclaration : 7 departements (layout dept). Autres pages : preferer layout hub pour /structure.',
                'blocks' => [[
                    'type'            => 'structures_grid',
                    'layout'          => 'dept',
                    'kicker'          => 'Organigramme',
                    'title'           => 'Structure organisationnelle',
                    'lead'            => 'Un noyau exécutif central appuyé par 7 départements spécialisés — Programme Paikady Taninjanaka.',
                    'banner_title'    => 'Structure organisationnelle — GoV Gen Z Madagascar',
                    'banner_subtitle' => '7 départements · Programme Paikady Taninjanaka',
                ]],
            ],
        ];
    }
}

