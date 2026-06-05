<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Models\DeclarationItemModel;
use CodeIgniter\Database\Migration;

/**
 * Programme Déclaration : fiches en BDD (comme positions) + page CMS bandeau seul (sans body_blocks).
 */
class SeedDeclarationItemsAndProgramCmsBody extends Migration
{
    private const TRANSLATION_GROUP = 'declaration-program-page';

    public function up(): void
    {
        $this->seedItems();
        $this->clearCmsBodiesForListHero();
    }

    public function down(): void
    {
        if ($this->db->tableExists('declaration_items')) {
            $this->db->table('declaration_items')->truncate();
        }
    }

    private function clearCmsBodiesForListHero(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $this->db->table('cms_pages')
            ->where('slug', 'declaration')
            ->where('translation_group', self::TRANSLATION_GROUP)
            ->update([
                'content_mode' => 'html',
                'body_html'    => '',
                'body_blocks'  => null,
                'layout_key'   => 'full',
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
    }

    private function seedItems(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($this->itemsFr() as $row) {
            $this->insertItemIfMissing($row, $now);
        }
        foreach ($this->itemsEn() as $row) {
            $this->insertItemIfMissing($row, $now);
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertItemIfMissing(array $row, string $now): void
    {
        $slug   = (string) ($row['slug'] ?? '');
        $locale = (string) ($row['locale'] ?? 'fr');
        if ($slug === '') {
            return;
        }

        $exists = $this->db->table('declaration_items')
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->get()
            ->getFirstRow() !== null;

        if ($exists) {
            return;
        }

        $row['created_at'] = $now;
        $row['updated_at'] = $now;
        $this->db->table('declaration_items')->insert($row);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function itemsFr(): array
    {
        return [
            $this->itemRow('fr', 'plaidoyer-jeunesse-decisions', 'decl-youth-decisions', DeclarationItemModel::SECTION_DECLARATIONS, DeclarationItemModel::KIND_PLEDGE, 10, 'Pour une jeunesse au cœur des décisions publiques', 'GoV Gen Z Madagascar appelle à l\'intégration systématique de la jeunesse malgache dans les processus de décision institutionnelle — budgets participatifs, conseils consultatifs, commissions législatives. Les 55 % de jeunes ne peuvent rester spectateurs de leur propre avenir.', 'Mai 2026 · Gouvernance', 'Plaidoyer', 'Soutenir ce plaidoyer', 'mailto:contact@govgenz.org', '2026-05-01 10:00:00'),
            $this->itemRow('fr', 'declaration-principes-2026', 'decl-principles-2026', DeclarationItemModel::SECTION_DECLARATIONS, DeclarationItemModel::KIND_OFFICIAL, 20, 'Déclaration de principes — GoV Gen Z Madagascar', 'GoV Gen Z Madagascar déclare son engagement total pour une gouvernance éthique, transparente et orientée vers le peuple malgache. Aucune décision ne sera prise sans documentation publique, aucune position ne sera défendue sans sources vérifiables.', 'Juin 2026 · Éthique & Transparence', 'Officiel', 'En savoir plus', 'mailto:contact@govgenz.org', '2026-06-01 10:00:00'),
            $this->itemRow('fr', 'alerte-contrats-miniers', 'decl-mining-alert', DeclarationItemModel::SECTION_DECLARATIONS, DeclarationItemModel::KIND_ALERT, 30, 'Alerte sur les contrats miniers opaques', 'GoV Gen Z Madagascar exprime ses vives préoccupations face à la signature de contrats d\'exploitation minière sans consultation publique ni transparence suffisante sur les retombées pour les communautés locales. Nous exigeons la publication intégrale des termes contractuels.', '2026 · Ressources naturelles', 'Alerte', 'Lire la position complète', 'mailto:contact@govgenz.org', '2026-01-15 10:00:00'),
            $this->itemRow('fr', 'partenariat-ptf', 'decl-partnership-ptf', DeclarationItemModel::SECTION_PARTNERSHIPS, DeclarationItemModel::KIND_PARTNERSHIP, 10, 'Recherche de partenaires techniques & financiers (PTF)', 'GoV Gen Z Madagascar est activement en discussion avec des organisations internationales, agences de développement et institutions bilatérales pour soutenir nos 6 projets prioritaires. Les partenariats seront annoncés ici dès leur signature officielle.', 'À venir · Développement', 'PTF', 'Proposer un partenariat', 'mailto:partnerships@govgenz.org'),
            $this->itemRow('fr', 'alliance-osc', 'decl-citizen-alliance', DeclarationItemModel::SECTION_PARTNERSHIPS, DeclarationItemModel::KIND_PARTNERSHIP, 20, 'Appel aux OSC, associations et collectifs citoyens', 'Nous recherchons des organisations de la société civile, associations et collectifs de jeunesse partageant nos valeurs pour amplifier notre impact terrain à Madagascar. Ensemble, nous pouvons construire une coalition citoyenne structurée et crédible.', 'À venir · Société civile', 'Alliance', 'Rejoindre la coalition', 'mailto:partnerships@govgenz.org'),
            $this->itemRow('fr', 'partenariat-institutionnel', 'decl-inst-partnership', DeclarationItemModel::SECTION_PARTNERSHIPS, DeclarationItemModel::KIND_PARTNERSHIP, 30, 'Collaborations avec ambassades, universités & centres de recherche', 'GoV Gen Z Madagascar cherche à établir des ponts avec des institutions académiques, des ambassades et des centres de recherche pour ancrer nos positions dans la rigueur intellectuelle et bénéficier d\'une légitimité internationale.', 'À venir · Institutions & Recherche', 'Institutionnel', 'Écrire à l’équipe partenariats', 'mailto:partnerships@govgenz.org'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function itemsEn(): array
    {
        return [
            $this->itemRow('en', 'youth-public-decisions-advocacy', 'decl-youth-decisions', DeclarationItemModel::SECTION_DECLARATIONS, DeclarationItemModel::KIND_PLEDGE, 10, 'Youth at the heart of public decisions', 'GoV Gen Z Madagascar calls for the systematic inclusion of Malagasy youth in institutional decision-making — participatory budgets, advisory councils, legislative committees. The 55% who are young cannot remain spectators of their own future.', 'May 2026 · Governance', 'Advocacy', 'Support this advocacy', 'mailto:contact@govgenz.org', '2026-05-01 10:00:00'),
            $this->itemRow('en', 'principles-declaration-2026', 'decl-principles-2026', DeclarationItemModel::SECTION_DECLARATIONS, DeclarationItemModel::KIND_OFFICIAL, 20, 'Principles declaration — GoV Gen Z Madagascar', 'GoV Gen Z Madagascar declares its full commitment to ethical, transparent governance oriented toward the Malagasy people. No decision without public documentation; no position without verifiable sources.', 'June 2026 · Ethics & transparency', 'Official', 'Learn more', 'mailto:contact@govgenz.org', '2026-06-01 10:00:00'),
            $this->itemRow('en', 'alert-opaque-mining', 'decl-mining-alert', DeclarationItemModel::SECTION_DECLARATIONS, DeclarationItemModel::KIND_ALERT, 30, 'Alert on opaque mining contracts', 'GoV Gen Z Madagascar expresses serious concern over mining contracts signed without adequate public consultation or transparency on benefits for local communities. We demand full publication of contractual terms.', '2026 · Natural resources', 'Alert', 'Read full position', 'mailto:contact@govgenz.org', '2026-01-15 10:00:00'),
            $this->itemRow('en', 'partnership-donors', 'decl-partnership-ptf', DeclarationItemModel::SECTION_PARTNERSHIPS, DeclarationItemModel::KIND_PARTNERSHIP, 10, 'Seeking technical & financial partners (donors)', 'GoV Gen Z Madagascar is in discussion with international organizations, development agencies and bilateral institutions to support our 6 priority projects. Partnerships will be announced here upon official signature.', 'Coming soon · Development', 'Donors', 'Propose a partnership', 'mailto:partnerships@govgenz.org'),
            $this->itemRow('en', 'citizen-alliance-call', 'decl-citizen-alliance', DeclarationItemModel::SECTION_PARTNERSHIPS, DeclarationItemModel::KIND_PARTNERSHIP, 20, 'Call to CSOs, associations and citizen collectives', 'We seek civil-society organizations, associations and youth collectives that share our values to amplify our field impact in Madagascar. Together we can build a structured, credible citizen coalition.', 'Coming soon · Civil society', 'Alliance', 'Join the coalition', 'mailto:partnerships@govgenz.org'),
            $this->itemRow('en', 'institutional-partnership', 'decl-inst-partnership', DeclarationItemModel::SECTION_PARTNERSHIPS, DeclarationItemModel::KIND_PARTNERSHIP, 30, 'Collaboration with embassies, universities & research centres', 'GoV Gen Z Madagascar seeks bridges with academic institutions, embassies and research centres to ground our positions in intellectual rigour and international legitimacy.', 'Coming soon · Institutions & research', 'Institutional', 'Email the partnerships team', 'mailto:partnerships@govgenz.org'),
        ];
    }

    private function itemRow(
        string $locale,
        string $slug,
        string $group,
        string $section,
        string $kind,
        int $sort,
        string $title,
        string $summary,
        string $metaLine,
        string $badge,
        string $ctaLabel,
        string $ctaHref,
        ?string $publishedAt = null,
    ): array {
        return [
            'slug'              => $slug,
            'locale'            => $locale,
            'translation_group' => $group,
            'title'             => $title,
            'summary'           => $summary,
            'kind'              => $kind,
            'list_section'      => $section,
            'meta_line'         => $metaLine,
            'band_label'        => '',
            'badge_label'       => $badge,
            'cta_label'         => $ctaLabel,
            'cta_href'          => $ctaHref,
            'sort_order'        => $sort,
            'publication_state' => DeclarationItemModel::PUBLICATION_PUBLISHED,
            'published_at'      => $publishedAt,
        ];
    }
}
