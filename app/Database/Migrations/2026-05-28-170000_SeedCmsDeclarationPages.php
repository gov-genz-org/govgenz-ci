<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Page programme Déclaration (FR/EN) — contenu en blocs CMS existants.
 */
class SeedCmsDeclarationPages extends Migration
{
    private const TRANSLATION_GROUP = 'declaration-program-page';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->insertPageIfMissing('declaration', 'fr', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Déclaration',
            'hero_overline'     => 'Déclaration publique',
            'hero_title'        => 'La parole officielle, documentée.',
            'hero_lead'         => 'Nos déclarations, plaidoyers et annonces de partenariats — pour un Madagascar meilleur, éthique et souverain.',
            'meta_title'        => 'Déclaration — GoV Gen Z Madagascar',
            'meta_description'  => 'Déclarations officielles, plaidoyers et annonces de partenariats — GoV Gen Z Madagascar.',
            'body_html'         => '',
            'status'            => 'published',
            'layout_key'        => 'full',
            'content_mode'      => 'blocks',
            'body_blocks'       => json_encode($this->blocksFr(), JSON_UNESCAPED_UNICODE),
            'hero_image_id'     => null,
            'hero_image_alt'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $this->insertPageIfMissing('declaration', 'en', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Declaration',
            'hero_overline'     => 'Public declaration',
            'hero_title'        => 'Official voice, documented.',
            'hero_lead'         => 'Our declarations, advocacy and partnership announcements — for a better, ethical and sovereign Madagascar.',
            'meta_title'        => 'Declaration — GoV Gen Z Madagascar',
            'meta_description'  => 'Official declarations, advocacy and partnership announcements — GoV Gen Z Madagascar.',
            'body_html'         => '',
            'status'            => 'published',
            'layout_key'        => 'full',
            'content_mode'      => 'blocks',
            'body_blocks'       => json_encode($this->blocksEn(), JSON_UNESCAPED_UNICODE),
            'hero_image_id'     => null,
            'hero_image_alt'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $this->seedNavItems($now);
    }

    public function down(): void
    {
        if ($this->db->tableExists('cms_pages')) {
            $this->db->table('cms_pages')
                ->where('translation_group', self::TRANSLATION_GROUP)
                ->delete();
        }

        if ($this->db->tableExists('site_nav_items')) {
            $builder = $this->db->table('site_nav_items')->where('match_key', 'declaration');
            if ($this->db->fieldExists('locale', 'site_nav_items')) {
                $builder->whereIn('locale', ['fr', 'en']);
            }
            $builder->delete();
        }
    }

    private function seedNavItems(string $now): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $this->insertNavIfMissing('fr', [
            'sort_order'  => 35,
            'label'       => 'Déclaration',
            'href_kind'   => 'segment',
            'href_target' => 'declaration',
            'match_key'   => 'declaration',
            'css_class'   => null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $this->insertNavIfMissing('en', [
            'sort_order'  => 35,
            'label'       => 'Declaration',
            'href_kind'   => 'segment',
            'href_target' => 'declaration',
            'match_key'   => 'declaration',
            'css_class'   => null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function blocksFr(): array
    {
        return [
            [
                'type'  => 'stats_grid',
                'stats' => [
                    ['value' => '3', 'suffix' => '', 'label' => 'Déclarations'],
                    ['value' => '2', 'suffix' => '', 'label' => 'Plaidoyers'],
                    ['value' => '14', 'suffix' => '', 'label' => 'Secteurs couverts'],
                    ['value' => '100', 'suffix' => '%', 'label' => 'Sourcé'],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Déclarations & Plaidoyers',
                    'Nos prises de position officielles, engagements publics et plaidoyers documentés — pour Madagascar.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [
                    [
                        'eyebrow'     => 'Plaidoyer',
                        'subtitle'    => 'Mai 2026 · Gouvernance',
                        'title'       => 'Pour une jeunesse au cœur des décisions publiques',
                        'description' => 'GoV Gen Z Madagascar appelle à l\'intégration systématique de la jeunesse malgache dans les processus de décision institutionnelle — budgets participatifs, conseils consultatifs, commissions législatives. Les 55 % de jeunes ne peuvent rester spectateurs de leur propre avenir.',
                        'href'        => 'mailto:contact@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Déclaration officielle',
                        'subtitle'    => 'Juin 2026 · Éthique & Transparence',
                        'title'       => 'Déclaration de principes — GoV Gen Z Madagascar',
                        'description' => 'GoV Gen Z Madagascar déclare son engagement total pour une gouvernance éthique, transparente et orientée vers le peuple malgache. Aucune décision ne sera prise sans documentation publique, aucune position ne sera défendue sans sources vérifiables.',
                        'href'        => 'mailto:contact@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Alerte publique',
                        'subtitle'    => '2026 · Ressources naturelles',
                        'title'       => 'Alerte sur les contrats miniers opaques',
                        'description' => 'GoV Gen Z Madagascar exprime ses vives préoccupations face à la signature de contrats d\'exploitation minière sans consultation publique ni transparence suffisante sur les retombées pour les communautés locales. Nous exigeons la publication intégrale des termes contractuels.',
                        'href'        => 'mailto:contact@govgenz.org',
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Organigramme',
                    'Structure organisationnelle — un noyau exécutif central appuyé par 7 départements spécialisés (Programme Paikady Taninjanaka).',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [
                    [
                        'title'       => 'Coordination',
                        'description' => 'Direction générale, alignement entre équipes, pilotage des décisions stratégiques du mouvement.',
                        'href'        => 'mailto:coordination@govgenz.org',
                    ],
                    [
                        'title'       => 'Sécurité',
                        'description' => 'Protection des membres, gestion des risques, sécurité des données et continuité des opérations.',
                        'href'        => 'mailto:securite@govgenz.org',
                    ],
                    [
                        'title'       => 'Communication',
                        'description' => 'Relations presse, réseaux sociaux, contenus publics, image institutionnelle du mouvement.',
                        'href'        => 'mailto:communication@govgenz.org',
                    ],
                    [
                        'title'       => 'Partenariats',
                        'description' => 'Relations avec les PTF, organisations internationales, ambassades et alliances citoyennes.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                    [
                        'title'       => 'Ressources humaines',
                        'description' => 'Recrutement des volontaires, formation, bien-être des membres, gestion des compétences.',
                        'href'        => 'mailto:rh@govgenz.org',
                    ],
                    [
                        'title'       => 'Project Management',
                        'description' => 'Suivi opérationnel des projets, coordination des équipes terrain, reporting et indicateurs.',
                        'href'        => 'mailto:projets@govgenz.org',
                    ],
                    [
                        'title'       => 'Finances',
                        'description' => 'Gestion transparente des ressources, budgets, audits internes et reporting financier.',
                        'href'        => 'mailto:finances@govgenz.org',
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Équipes de terrain',
                    '14 équipes sectorielles — chaque secteur clé de Madagascar est couvert par une équipe dédiée.',
                ],
            ],
            ['type' => 'sectors_grid'],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Identité',
                    'L\'ADN de GoV Gen Z Madagascar — nos fondements : pour qui nous agissons, ce qui nous guide, comment nous travaillons, et vers quoi nous allons.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'pillar_cards',
                'cards'   => [
                    [
                        'eyebrow' => 'Pour qui',
                        'title'   => 'Notre raison d\'être',
                        'bullets' => ['55 % de jeunes malgaches', '12 à 35 ans', 'La diaspora', 'Les générations futures'],
                    ],
                    [
                        'eyebrow' => 'Ce qui nous guide',
                        'title'   => 'Nos valeurs',
                        'bullets' => ['Intégrité · Éthique', 'Rigueur · Honnêteté', 'Honneur · Efficacité', 'Envie de mieux'],
                    ],
                    [
                        'eyebrow' => 'Comment',
                        'title'   => 'Notre méthode',
                        'bullets' => ['Intelligence collective', 'Documentation transparente', 'Décisions participatives', 'Impact réel et mesurable'],
                    ],
                    [
                        'eyebrow' => 'Pour quoi',
                        'title'   => 'Notre but',
                        'bullets' => ['Dignité & sérénité', 'Gouvernance nationale', 'Services publics fonctionnels', 'Avenir meilleur et durable'],
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Annonces de partenariats',
                    'Partenariats & Alliances — nos annonces officielles de collaboration avec des organisations, institutions et réseaux citoyens qui partagent nos valeurs.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [
                    [
                        'eyebrow'     => 'Partenariat technique & financier',
                        'subtitle'    => 'À venir · Développement',
                        'title'       => 'Recherche de partenaires techniques & financiers (PTF)',
                        'description' => 'GoV Gen Z Madagascar est activement en discussion avec des organisations internationales, agences de développement et institutions bilatérales pour soutenir nos 6 projets prioritaires. Les partenariats seront annoncés ici dès leur signature officielle.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Alliance citoyenne',
                        'subtitle'    => 'À venir · Société civile',
                        'title'       => 'Appel aux OSC, associations et collectifs citoyens',
                        'description' => 'Nous recherchons des organisations de la société civile, associations et collectifs de jeunesse partageant nos valeurs pour amplifier notre impact terrain à Madagascar. Ensemble, nous pouvons construire une coalition citoyenne structurée et crédible.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Partenariat institutionnel',
                        'subtitle'    => 'À venir · Institutions & Recherche',
                        'title'       => 'Collaborations avec ambassades, universités & centres de recherche',
                        'description' => 'GoV Gen Z Madagascar cherche à établir des ponts avec des institutions académiques, des ambassades et des centres de recherche pour ancrer nos positions dans la rigueur intellectuelle et bénéficier d\'une légitimité internationale.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Politiques internes',
                    'Nos engagements éthiques — les principes qui régissent notre action et garantissent notre crédibilité institutionnelle.',
                ],
            ],
            [
                'type'     => 'legal_prose',
                'sections' => [
                    [
                        'heading' => 'Charte éthique',
                        'body'    => 'GoV Gen Z Madagascar s\'engage à agir dans le respect strict de l\'éthique institutionnelle :',
                        'bullets' => [
                            'Professionnalisme dans toutes les communications publiques',
                            'Absence d\'accusations non vérifiées ou de propos diffamatoires',
                            'Ton institutionnel, pacifique et non violent en toutes circonstances',
                            'Sources citées et vérifiables pour toute affirmation publique',
                            'Séparation claire entre analyse et opinion',
                        ],
                    ],
                    [
                        'heading' => 'Confidentialité & données personnelles',
                        'body'    => 'La protection des données de nos membres est une priorité absolue :',
                        'bullets' => [
                            'Aucune donnée personnelle publiée sans consentement explicite',
                            'Listes d\'inscrits strictement internes — seul le nombre total est public',
                            'Contacts limités aux secteurs et organisations, sans données sensibles',
                            'Conformité RGPD pour tous les traitements numériques',
                        ],
                    ],
                    [
                        'heading' => 'Conflits d\'intérêts',
                        'body'    => 'GoV Gen Z Madagascar maintient une indépendance totale vis-à-vis des intérêts partisans :',
                        'bullets' => [
                            'Aucun membre ne peut représenter simultanément un parti politique',
                            'Déclaration obligatoire de tout conflit d\'intérêts potentiel',
                            'Récusation systématique en cas de conflit identifié',
                            'Audit interne périodique des positions et décisions',
                        ],
                    ],
                    [
                        'heading' => 'Transparence financière',
                        'body'    => 'La gestion financière est soumise aux plus hauts standards de transparence :',
                        'bullets' => [
                            'Publication régulière des rapports financiers',
                            'Audit indépendant annuel des comptes',
                            'Traçabilité complète des dons et financements reçus',
                            'Aucune dépense non documentée ou non approuvée',
                        ],
                    ],
                ],
            ],
            [
                'type'    => 'cta_panel',
                'text'    => 'Une question sur nos déclarations ou partenariats ?',
                'actions' => [
                    ['label' => 'contact@govgenz.org', 'href' => 'mailto:contact@govgenz.org', 'variant' => 'primary'],
                    ['label' => 'partnerships@govgenz.org', 'href' => 'mailto:partnerships@govgenz.org', 'variant' => 'secondary'],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function blocksEn(): array
    {
        return [
            [
                'type'  => 'stats_grid',
                'stats' => [
                    ['value' => '3', 'suffix' => '', 'label' => 'Declarations'],
                    ['value' => '2', 'suffix' => '', 'label' => 'Advocacy pieces'],
                    ['value' => '14', 'suffix' => '', 'label' => 'Sectors covered'],
                    ['value' => '100', 'suffix' => '%', 'label' => 'Sourced'],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Declarations & advocacy',
                    'Our official positions, public commitments and documented advocacy — for Madagascar.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [
                    [
                        'eyebrow'     => 'Advocacy',
                        'subtitle'    => 'May 2026 · Governance',
                        'title'       => 'Youth at the heart of public decisions',
                        'description' => 'GoV Gen Z Madagascar calls for the systematic inclusion of Malagasy youth in institutional decision-making — participatory budgets, advisory councils, legislative committees. The 55% who are young cannot remain spectators of their own future.',
                        'href'        => 'mailto:contact@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Official declaration',
                        'subtitle'    => 'June 2026 · Ethics & transparency',
                        'title'       => 'Principles declaration — GoV Gen Z Madagascar',
                        'description' => 'GoV Gen Z Madagascar declares its full commitment to ethical, transparent governance oriented toward the Malagasy people. No decision without public documentation; no position without verifiable sources.',
                        'href'        => 'mailto:contact@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Public alert',
                        'subtitle'    => '2026 · Natural resources',
                        'title'       => 'Alert on opaque mining contracts',
                        'description' => 'GoV Gen Z Madagascar expresses serious concern over mining contracts signed without adequate public consultation or transparency on benefits for local communities. We demand full publication of contractual terms.',
                        'href'        => 'mailto:contact@govgenz.org',
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Organization chart',
                    'Organizational structure — a central executive core supported by 7 specialized departments (Paikady Taninjanaka programme).',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [
                    [
                        'title'       => 'Coordination',
                        'description' => 'General management, team alignment, strategic decision-making for the movement.',
                        'href'        => 'mailto:coordination@govgenz.org',
                    ],
                    [
                        'title'       => 'Security',
                        'description' => 'Member protection, risk management, data security and operational continuity.',
                        'href'        => 'mailto:securite@govgenz.org',
                    ],
                    [
                        'title'       => 'Communication',
                        'description' => 'Press relations, social media, public content, institutional image.',
                        'href'        => 'mailto:communication@govgenz.org',
                    ],
                    [
                        'title'       => 'Partnerships',
                        'description' => 'Relations with donors, international organizations, embassies and citizen alliances.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                    [
                        'title'       => 'Human resources',
                        'description' => 'Volunteer recruitment, training, member wellbeing, skills management.',
                        'href'        => 'mailto:rh@govgenz.org',
                    ],
                    [
                        'title'       => 'Project management',
                        'description' => 'Operational project follow-up, field team coordination, reporting and indicators.',
                        'href'        => 'mailto:projets@govgenz.org',
                    ],
                    [
                        'title'       => 'Finance',
                        'description' => 'Transparent resource management, budgets, internal audits and financial reporting.',
                        'href'        => 'mailto:finances@govgenz.org',
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Field teams',
                    '14 sector teams — each key sector in Madagascar is covered by a dedicated team.',
                ],
            ],
            ['type' => 'sectors_grid'],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Identity',
                    'The DNA of GoV Gen Z Madagascar — who we act for, what guides us, how we work, and where we are headed.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'pillar_cards',
                'cards'   => [
                    [
                        'eyebrow' => 'For whom',
                        'title'   => 'Our purpose',
                        'bullets' => ['55% young Malagasy', 'Ages 12–35', 'The diaspora', 'Future generations'],
                    ],
                    [
                        'eyebrow' => 'What guides us',
                        'title'   => 'Our values',
                        'bullets' => ['Integrity · Ethics', 'Rigor · Honesty', 'Honor · Efficiency', 'Drive to improve'],
                    ],
                    [
                        'eyebrow' => 'How',
                        'title'   => 'Our method',
                        'bullets' => ['Collective intelligence', 'Transparent documentation', 'Participatory decisions', 'Real, measurable impact'],
                    ],
                    [
                        'eyebrow' => 'Why',
                        'title'   => 'Our goal',
                        'bullets' => ['Dignity & serenity', 'National governance', 'Working public services', 'A better, sustainable future'],
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Partnership announcements',
                    'Partnerships & alliances — official collaboration announcements with organizations and networks that share our values.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [
                    [
                        'eyebrow'     => 'Technical & financial partnership',
                        'subtitle'    => 'Coming soon · Development',
                        'title'       => 'Seeking technical & financial partners (donors)',
                        'description' => 'GoV Gen Z Madagascar is in discussion with international organizations, development agencies and bilateral institutions to support our 6 priority projects. Partnerships will be announced here upon official signature.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Citizen alliance',
                        'subtitle'    => 'Coming soon · Civil society',
                        'title'       => 'Call to CSOs, associations and citizen collectives',
                        'description' => 'We seek civil-society organizations, associations and youth collectives that share our values to amplify our field impact in Madagascar. Together we can build a structured, credible citizen coalition.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                    [
                        'eyebrow'     => 'Institutional partnership',
                        'subtitle'    => 'Coming soon · Institutions & research',
                        'title'       => 'Collaboration with embassies, universities & research centres',
                        'description' => 'GoV Gen Z Madagascar seeks bridges with academic institutions, embassies and research centres to ground our positions in intellectual rigour and international legitimacy.',
                        'href'        => 'mailto:partnerships@govgenz.org',
                    ],
                ],
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Internal policies',
                    'Our ethical commitments — principles that govern our action and safeguard institutional credibility.',
                ],
            ],
            [
                'type'     => 'legal_prose',
                'sections' => [
                    [
                        'heading' => 'Ethics charter',
                        'body'    => 'GoV Gen Z Madagascar commits to strict institutional ethics:',
                        'bullets' => [
                            'Professionalism in all public communications',
                            'No unverified accusations or defamatory statements',
                            'Institutional, peaceful and non-violent tone at all times',
                            'Cited, verifiable sources for every public claim',
                            'Clear separation between analysis and opinion',
                        ],
                    ],
                    [
                        'heading' => 'Privacy & personal data',
                        'body'    => 'Protecting member data is an absolute priority:',
                        'bullets' => [
                            'No personal data published without explicit consent',
                            'Registration lists are internal only — only totals are public',
                            'Contacts limited to sectors and organizations, no sensitive data',
                            'GDPR compliance for all digital processing',
                        ],
                    ],
                    [
                        'heading' => 'Conflicts of interest',
                        'body'    => 'GoV Gen Z Madagascar maintains full independence from partisan interests:',
                        'bullets' => [
                            'No member may simultaneously represent a political party',
                            'Mandatory disclosure of any potential conflict of interest',
                            'Systematic recusal when a conflict is identified',
                            'Periodic internal audit of positions and decisions',
                        ],
                    ],
                    [
                        'heading' => 'Financial transparency',
                        'body'    => 'Financial management meets the highest transparency standards:',
                        'bullets' => [
                            'Regular publication of financial reports',
                            'Annual independent audit of accounts',
                            'Full traceability of donations and funding received',
                            'No undocumented or unapproved expenditure',
                        ],
                    ],
                ],
            ],
            [
                'type'    => 'cta_panel',
                'text'    => 'Questions about our declarations or partnerships?',
                'actions' => [
                    ['label' => 'contact@govgenz.org', 'href' => 'mailto:contact@govgenz.org', 'variant' => 'primary'],
                    ['label' => 'partnerships@govgenz.org', 'href' => 'mailto:partnerships@govgenz.org', 'variant' => 'secondary'],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertPageIfMissing(string $slug, string $locale, array $row): void
    {
        $exists = $this->db->table('cms_pages')
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->get()
            ->getFirstRow() !== null;

        if ($exists) {
            return;
        }

        $row['slug']   = $slug;
        $row['locale'] = $locale;

        $fieldData = $this->db->getFieldData('cms_pages');
        $names     = [];
        foreach ($fieldData as $f) {
            $n = is_object($f) ? ($f->name ?? null) : ($f['name'] ?? null);
            if (is_string($n) && $n !== '' && $n !== 'id') {
                $names[] = $n;
            }
        }

        $out = [];
        foreach ($names as $name) {
            if (array_key_exists($name, $row)) {
                $out[$name] = $row[$name];
            }
        }

        $this->db->table('cms_pages')->insert($out);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertNavIfMissing(string $locale, array $row): void
    {
        $matchKey = (string) ($row['match_key'] ?? '');
        if ($matchKey === '') {
            return;
        }

        $builder = $this->db->table('site_nav_items')->where('match_key', $matchKey);
        if ($this->db->fieldExists('locale', 'site_nav_items')) {
            $builder->where('locale', $locale);
        }

        if ($builder->get()->getFirstRow() !== null) {
            return;
        }

        if ($this->db->fieldExists('locale', 'site_nav_items')) {
            $row['locale'] = $locale;
        }

        $this->db->table('site_nav_items')->insert($row);
    }
}
