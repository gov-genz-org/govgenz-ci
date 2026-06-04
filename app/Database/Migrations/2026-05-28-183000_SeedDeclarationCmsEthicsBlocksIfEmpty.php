<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Réinjecte le bloc « engagements éthiques » (accordéon) si la page declaration n’a plus de body_blocks.
 */
class SeedDeclarationCmsEthicsBlocksIfEmpty extends Migration
{
    private const TRANSLATION_GROUP = 'declaration-program-page';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        foreach (['fr' => $this->ethicsBlocksFr(), 'en' => $this->ethicsBlocksEn()] as $locale => $blocks) {
            $row = $this->db->table('cms_pages')
                ->where('slug', 'declaration')
                ->where('locale', $locale)
                ->where('translation_group', self::TRANSLATION_GROUP)
                ->get()
                ->getRowArray();

            if (! is_array($row)) {
                continue;
            }

            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw !== '' && $raw !== '[]' && $raw !== 'null') {
                continue;
            }

            $this->db->table('cms_pages')
                ->where('id', (int) $row['id'])
                ->update([
                    'content_mode' => 'blocks',
                    'body_blocks'  => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                    'body_html'    => '',
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
        }
    }

    public function down(): void
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ethicsBlocksFr(): array
    {
        return [
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
                'sections'     => [
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
    private function ethicsBlocksEn(): array
    {
        return [
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
                'sections'     => [
                    [
                        'heading' => 'Ethics charter',
                        'body'    => 'GoV Gen Z Madagascar commits to strict institutional ethics:',
                        'bullets' => [
                            'Professionalism in all public communications',
                            'No unverified accusations',
                            'Peaceful institutional tone',
                            'Verifiable sources',
                            'Clear separation between analysis and opinion',
                        ],
                    ],
                    [
                        'heading' => 'Privacy & personal data',
                        'body'    => 'Protecting member data is an absolute priority:',
                        'bullets' => [
                            'No personal data without consent',
                            'Internal registration lists only',
                            'GDPR compliance',
                        ],
                    ],
                    [
                        'heading' => 'Conflicts of interest',
                        'body'    => 'Full independence from partisan interests:',
                        'bullets' => [
                            'No simultaneous party representation',
                            'Mandatory conflict disclosure',
                            'Systematic recusal',
                        ],
                    ],
                    [
                        'heading' => 'Financial transparency',
                        'body'    => 'Financial management meets the highest standards:',
                        'bullets' => [
                            'Regular financial reports',
                            'Annual independent audit',
                            'Full traceability of funding',
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
}
