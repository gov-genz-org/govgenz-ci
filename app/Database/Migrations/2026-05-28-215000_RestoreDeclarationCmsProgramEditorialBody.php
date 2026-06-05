<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Page programme declaration : corps éditorial complet (grilles BDD, ADN, éthique, CTA).
 * Sans stats ni cartes déclaration (liste BDD) ni partenariats (liste BDD).
 */
class RestoreDeclarationCmsProgramEditorialBody extends Migration
{
    private const TRANSLATION_GROUP = 'declaration-program-page';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        foreach (['fr' => $this->blocksFr(), 'en' => $this->blocksEn()] as $locale => $blocks) {
            $row = $this->db->table('cms_pages')
                ->where('slug', 'declaration')
                ->where('locale', $locale)
                ->where('translation_group', self::TRANSLATION_GROUP)
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                continue;
            }

            $this->db->table('cms_pages')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'content_mode' => 'blocks',
                    'body_html'    => '',
                    'body_blocks'  => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);

            CmsPublishedPageCache::forget($locale, 'declaration');
        }
    }

    public function down(): void
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function blocksFr(): array
    {
        return [
            [
                'type'            => 'structures_grid',
                'layout'          => 'dept',
                'kicker'          => 'Organigramme',
                'title'           => 'Structure organisationnelle',
                'lead'            => 'Un noyau exécutif central appuyé par 7 départements spécialisés — Programme Paikady Taninjanaka.',
                'banner_title'    => 'Structure organisationnelle — GoV Gen Z Madagascar',
                'banner_subtitle' => '7 départements · Programme Paikady Taninjanaka',
            ],
            [
                'type'            => 'sectors_grid',
                'layout'          => 'compact',
                'kicker'          => 'Équipes de terrain',
                'title'           => '14 équipes sectorielles',
                'lead'            => 'Chaque secteur clé de Madagascar est couvert par une équipe dédiée — bâtir, innover, servir le peuple.',
                'banner_title'    => 'Équipes sectorielles — GoV Gen Z Madagascar',
                'banner_subtitle' => '14 secteurs · Madagascar',
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Identité',
                    'L\'ADN de GoV Gen Z Madagascar',
                    'Nos fondements — pour qui nous agissons, ce qui nous guide, comment nous travaillons, et vers quoi nous allons.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'pillar_cards',
                'cards'   => $this->adnCardsFr(),
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Politiques internes',
                    'Nos engagements éthiques',
                    'Les principes qui régissent notre action et garantissent notre crédibilité institutionnelle.',
                ],
            ],
            [
                'type'         => 'legal_prose',
                'presentation' => 'accordion',
                'sections'     => $this->ethicsSectionsFr(),
            ],
            [
                'type'    => 'cta_panel',
                'text'    => 'Une question sur nos déclarations ou partenariats ?',
                'actions' => [
                    ['label' => 'Écrire au mouvement', 'href' => 'mailto:contact@govgenz.org', 'variant' => 'primary'],
                    ['label' => 'Partenariats & alliances', 'href' => 'mailto:partnerships@govgenz.org', 'variant' => 'secondary'],
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
                'type'            => 'structures_grid',
                'layout'          => 'dept',
                'kicker'          => 'Organization chart',
                'title'           => 'Organizational structure',
                'lead'            => 'A central executive core supported by 7 specialized departments — Paikady Taninjanaka programme.',
                'banner_title'    => 'Organizational structure — GoV Gen Z Madagascar',
                'banner_subtitle' => '7 departments · Paikady Taninjanaka program',
            ],
            [
                'type'            => 'sectors_grid',
                'layout'          => 'compact',
                'kicker'          => 'Field teams',
                'title'           => '14 sector teams',
                'lead'            => 'Each key sector in Madagascar is covered by a dedicated team — build, innovate, serve the people.',
                'banner_title'    => 'Sector teams — GoV Gen Z Madagascar',
                'banner_subtitle' => '14 sectors · Madagascar',
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Identity',
                    'The DNA of GoV Gen Z Madagascar',
                    'Our foundations — who we act for, what guides us, how we work, and where we are headed.',
                ],
            ],
            [
                'type'    => 'cards_grid',
                'variant' => 'pillar_cards',
                'cards'   => $this->adnCardsEn(),
            ],
            [
                'type'       => 'section_text',
                'paragraphs' => [
                    'Internal policies',
                    'Our ethical commitments',
                    'Principles that govern our action and safeguard institutional credibility.',
                ],
            ],
            [
                'type'         => 'legal_prose',
                'presentation' => 'accordion',
                'sections'     => $this->ethicsSectionsEn(),
            ],
            [
                'type'    => 'cta_panel',
                'text'    => 'Questions about our declarations or partnerships?',
                'actions' => [
                    ['label' => 'Write to the movement', 'href' => 'mailto:contact@govgenz.org', 'variant' => 'primary'],
                    ['label' => 'Partnerships & alliances', 'href' => 'mailto:partnerships@govgenz.org', 'variant' => 'secondary'],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function adnCardsFr(): array
    {
        return [
            ['eyebrow' => 'Pour qui', 'title' => 'Notre raison d\'être', 'bullets' => ['55 % de jeunes malgaches', '12 à 35 ans', 'La diaspora', 'Les générations futures']],
            ['eyebrow' => 'Ce qui nous guide', 'title' => 'Nos valeurs', 'bullets' => ['Intégrité · Éthique', 'Rigueur · Honnêteté', 'Honneur · Efficacité', 'Envie de mieux']],
            ['eyebrow' => 'Comment', 'title' => 'Notre méthode', 'bullets' => ['Intelligence collective', 'Documentation transparente', 'Décisions participatives', 'Impact réel et mesurable']],
            ['eyebrow' => 'Pour quoi', 'title' => 'Notre but', 'bullets' => ['Dignité & sérénité', 'Gouvernance nationale', 'Services publics fonctionnels', 'Avenir meilleur et durable']],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function adnCardsEn(): array
    {
        return [
            ['eyebrow' => 'For whom', 'title' => 'Our purpose', 'bullets' => ['55% young Malagasy', 'Ages 12–35', 'The diaspora', 'Future generations']],
            ['eyebrow' => 'What guides us', 'title' => 'Our values', 'bullets' => ['Integrity · Ethics', 'Rigor · Honesty', 'Honor · Efficiency', 'Drive to improve']],
            ['eyebrow' => 'How', 'title' => 'Our method', 'bullets' => ['Collective intelligence', 'Transparent documentation', 'Participatory decisions', 'Real, measurable impact']],
            ['eyebrow' => 'Why', 'title' => 'Our goal', 'bullets' => ['Dignity & serenity', 'National governance', 'Working public services', 'A better, sustainable future']],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ethicsSectionsFr(): array
    {
        return [
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
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ethicsSectionsEn(): array
    {
        return [
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
        ];
    }
}
