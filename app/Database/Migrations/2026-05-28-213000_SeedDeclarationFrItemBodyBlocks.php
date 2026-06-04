<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fiches déclaration (FR) : corps détaillé en blocs pour les 3 déclarations + 3 partenariats.
 */
class SeedDeclarationFrItemBodyBlocks extends Migration
{
    /** @var array<string, list<array<string, mixed>>> */
    private const BLOCKS_BY_SLUG = [
        'plaidoyer-jeunesse-decisions' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Pourquoi inclure la jeunesse',
                'heading_style'    => 'warm',
                'intro'            => 'Plus de la moitié de la population malgache a moins de 25 ans. Pourtant, les jeunes restent sous-représentés dans les débats budgétaires, les commissions législatives et les instances de planification qui dessinent leur avenir.',
                'bullets'          => [
                    '55 % de Malagasy sont jeunes — projections démographiques INSTAT',
                    'Peu de budgets participatifs incluent des délégués de moins de 30 ans',
                    'Les conseils consultatifs publient rarement comptes rendus et retours citoyens',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'Ce que nous demandons aux institutions',
                'heading_style'    => 'teal',
                'intro'            => 'GoV Gen Z Madagascar appelle à des mécanismes d’inclusion concrets et mesurables — pas des forums jeunesse symboliques.',
                'bullets'          => [
                    'Réserver des sièges jeunesse aux commissions législatives sur l’éducation, l’emploi et le numérique',
                    'Lancer des budgets participatifs pilotes dans au moins 5 régions avec résultats publiés',
                    'Publier ordres du jour, comptes rendus et suites des conseils consultatifs sous 30 jours',
                    'Suivre des indicateurs d’inclusion jeunesse dans les rapports annuels de gouvernance',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Rejoindre le plaidoyer',
                'heading_style' => 'teal',
                'message'       => 'Institutions, médias et citoyens : contactez-nous pour co-signer ce plaidoyer ou proposer un mécanisme d’inclusion concret dans votre secteur.',
                'submessage'    => 'contact@govgenz.org · Objet : Plaidoyer inclusion jeunesse',
            ],
            [
                'type'          => 'sources',
                'section_title' => 'Sources et références',
                'lines'         => [
                    'INSTAT — Perspectives démographiques Madagascar (projections 2024–2026)',
                    'PNUD — Jeunesse, gouvernance et participation citoyenne en Afrique (2023)',
                    'GoV Gen Z Madagascar — cartographie interne secteurs, programme Gouvernance (2026)',
                ],
            ],
        ],
        'declaration-principes-2026' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Nos engagements publics',
                'heading_style'    => 'teal',
                'intro'            => 'Cette déclaration de principes fixe la base éthique de chaque position, projet et partenariat annoncé par GoV Gen Z Madagascar.',
                'bullets'          => [
                    'Documenter toute décision publique avec des sources vérifiables avant tout plaidoyer',
                    'Refuser les accords opaques qui contournent la consultation des communautés',
                    'Prioriser la souveraineté malgache en matière de numérique, ressources naturelles et éducation',
                    'Publier les termes des partenariats dès leur signature',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'Ce que cela signifie concrètement',
                'heading_style'    => 'warm',
                'intro'            => '',
                'bullets'          => [],
                'extra_paragraphs' => [
                    'Chaque carte de cette page renvoie vers une fiche détaillée documentée — résumé dans le hero, contexte complet en blocs ci-dessous.',
                    'Positions et projets suivent la même exigence : pas d’affirmation anonyme, pas de statistique invérifiable.',
                    'Quand nous manquons de données, nous le disons publiquement et invitons les experts à contribuer.',
                ],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Document vivant',
                'heading_style' => 'warm',
                'message'       => 'Ces principes sont revus par les instances de gouvernance du mouvement et mis à jour lorsque le terrain révèle de nouvelles exigences éthiques.',
                'submessage'    => 'Dernière révision : juin 2026',
            ],
        ],
        'alerte-contrats-miniers' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Contexte',
                'heading_style'    => 'warm',
                'intro'            => 'Des contrats miniers signés sans consultation publique suffisante sapent la confiance des communautés et la transparence fiscale. Les populations locales apprennent souvent l’existence des concessions une fois les opérations lancées.',
                'bullets'          => [],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'Nos préoccupations',
                'heading_style'    => 'warm',
                'intro'            => '',
                'bullets'          => [
                    'Termes contractuels non publiés intégralement avant ratification parlementaire',
                    'Divulgation insuffisante sur le partage des revenus avec les communautés affectées',
                    'Études d’impact environnemental inaccessibles en langues locales',
                    'Absence de piste d’audit indépendante sur les redevances',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'             => 'section_rich',
                'heading'          => 'Ce que nous exigeons',
                'heading_style'    => 'teal',
                'intro'            => 'GoV Gen Z Madagascar appelle à des mesures de transparence immédiates avant toute ratification de nouvelle concession minière.',
                'bullets'          => [
                    'Publier en ligne les textes contractuels complets et leurs annexes',
                    'Organiser des audiences publiques dans les régions concernées avec comptes rendus documentés',
                    'Mettre en place un mécanisme indépendant de suivi des retombées communautaires',
                    'Aligner les standards de publication sur les principes ITIE',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'sources',
                'section_title' => 'Sources',
                'lines'         => [
                    'ITIE — Standard de transparence des industries extractives',
                    'Banque mondiale — Gouvernance des ressources naturelles (note pays Madagascar)',
                    'GoV Gen Z Madagascar — notes terrain équipe Ressources naturelles (2026)',
                ],
            ],
        ],
        'partenariat-ptf' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Périmètre du partenariat',
                'heading_style'    => 'teal',
                'intro'            => 'Nous recherchons des partenaires techniques et financiers alignés sur nos six projets prioritaires : gouvernance, éducation, souveraineté numérique, santé, environnement et emploi des jeunes.',
                'bullets'          => [
                    'Financement pluriannuel avec exigences de reporting public',
                    'Co-conception avec des équipes malgaches pilotées par la jeunesse — pas de modèles imposés',
                    'Transparence sur les frais de structure et la sous-traitance locale',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Proposer un partenariat',
                'heading_style' => 'teal',
                'message'       => 'Organisations internationales, agences de développement et institutions bilatérales : contactez-nous avec une note conceptuelle. Les partenariats signés seront annoncés sur cette page.',
                'submessage'    => 'partnerships@govgenz.org',
            ],
        ],
        'alliance-osc' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Qui nous invitons',
                'heading_style'    => 'warm',
                'intro'            => 'OSC, associations de jeunesse, collectifs citoyens et médias locaux partageant nos valeurs de gouvernance éthique et de plaidoyer documenté.',
                'bullets'          => [
                    'Missions terrain conjointes et vérification des sources',
                    'Formations partagées à l’analyse des politiques publiques',
                    'Déclarations de coalition avec signataires identifiés',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Rejoindre la coalition',
                'heading_style' => 'warm',
                'message'       => 'Présentez votre organisation, votre zone d’action et une idée concrète de collaboration. Réponse sous 10 jours ouvrés.',
                'submessage'    => 'partnerships@govgenz.org · Objet : Alliance citoyenne',
            ],
        ],
        'partenariat-institutionnel' => [
            [
                'type'             => 'section_rich',
                'heading'          => 'Collaboration institutionnelle',
                'heading_style'    => 'teal',
                'intro'            => 'Universités, centres de recherche et missions diplomatiques peuvent ancrer nos positions dans la rigueur analytique et les bonnes pratiques internationales.',
                'bullets'          => [
                    'Relecture experte des positions sectorielles avant publication',
                    'Partenariats de recherche étudiante avec livrables crédités',
                    'Conférences publiques et notes de politique à Antananarivo et en régions',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'note_panel',
                'section_title' => 'Contact',
                'heading_style' => 'teal',
                'message'       => 'Ambassades, facultés et instituts de recherche : présentez votre institution et le format de collaboration envisagé.',
                'submessage'    => 'partnerships@govgenz.org · Objet : Partenariat institutionnel',
            ],
        ],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }
        if (! $this->db->fieldExists('body_blocks', 'declaration_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (self::BLOCKS_BY_SLUG as $slug => $blocks) {
            $row = $this->db->table('declaration_items')
                ->where('slug', $slug)
                ->where('locale', 'fr')
                ->get()
                ->getRowArray();

            if ($row === null) {
                continue;
            }

            $existing = trim((string) ($row['body_blocks'] ?? ''));
            if ($existing !== '' && $existing !== '[]') {
                continue;
            }

            $this->db->table('declaration_items')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'body'              => null,
                    'body_content_mode' => 'blocks',
                    'body_blocks'       => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                    'updated_at'        => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (array_keys(self::BLOCKS_BY_SLUG) as $slug) {
            $this->db->table('declaration_items')
                ->where('slug', $slug)
                ->where('locale', 'fr')
                ->update([
                    'body'              => null,
                    'body_content_mode' => 'blocks',
                    'body_blocks'       => null,
                    'updated_at'        => $now,
                ]);
        }
    }
}
