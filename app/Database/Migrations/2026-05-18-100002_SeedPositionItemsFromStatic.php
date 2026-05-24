<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedPositionItemsFromStatic extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('position_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $blocksFr = json_encode([
            [
                'type'          => 'section_rich',
                'heading'       => '📰 Contexte officiel',
                'heading_style' => 'warm',
                'intro'         => 'Le gouvernement a annoncé un plan d\'éducation quinquennal doté de 500 milliards d\'Ariary, promettant la construction de 2 000 salles de classe, l\'équipement de 500 écoles en tablettes et la revalorisation salariale des enseignants.',
                'bullets'       => [],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'section_rich',
                'heading'       => '🔍 Ce que l\'annonce ne dit pas',
                'heading_style' => 'warm',
                'intro'         => '',
                'bullets'       => [
                    'Ce qu\'on voit : budget de 500 Mds Ar et chiffres de construction',
                    'Ce qu\'on ne voit pas : 67 % des écoles rurales sans électricité',
                    'Manque critique : aucune formation pédagogique des enseignants',
                    'Risque financier : pas de mécanisme de suivi budgétaire ni d\'audit indépendant',
                ],
                'extra_paragraphs' => [],
            ],
            [
                'type'          => 'section_rich',
                'heading'       => '✅ La position de GoV Gen Z Madagascar',
                'heading_style' => 'teal',
                'intro'         => 'Des tablettes dans des écoles sans électricité ne constituent pas un plan d\'éducation. Nous demandons une révision du plan avec trois piliers chiffrés : électrification solaire, formation pédagogique et redevabilité publique.',
                'bullets'       => [
                    'Pilier 1 — Électrification scolaire prioritaire : 180 Mds Ar · 18 mois',
                    'Pilier 2 — Formation pédagogique : 120 Mds Ar · 24 mois',
                    'Pilier 3 — Mécanisme de redevabilité : 30 Mds Ar · continu',
                ],
                'extra_paragraphs' => [],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $this->insertIfMissing('plan-education-2026-2030', 'fr', [
            'translation_group' => 'plan-education-2026',
            'title'             => 'Plan national d\'éducation 2026–2030 : ce que l\'annonce ne dit pas',
            'excerpt'           => 'Alerte sur le plan Éducation 2026–2030 et proposition de trois piliers chiffrés.',
            'summary'           => 'Des tablettes dans des écoles sans électricité ne constituent pas un plan d\'éducation. GoV Gen Z propose un séquençage en 3 piliers chiffrés : électrification solaire en priorité, formation pédagogique obligatoire pour 15 000 enseignants, et mécanisme de redevabilité public.',
            'body'              => null,
            'body_content_mode' => 'blocks',
            'body_blocks'       => $blocksFr,
            'types_csv'         => 'denial,solution',
            'sectors_csv'       => 'education,digital',
            'reading_minutes'   => 6,
            'publication_state' => 'published',
            'meta_title'        => 'Plan éducation 2026–2030 — GoV Gen Z',
            'meta_description'  => 'Analyse et contre-proposition sur le plan national d\'éducation 2026–2030.',
            'published_at'      => '2026-05-12 10:00:00',
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('position_items')) {
            return;
        }

        $this->db->table('position_items')
            ->where('slug', 'plan-education-2026-2030')
            ->where('locale', 'fr')
            ->delete();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertIfMissing(string $slug, string $locale, array $row): void
    {
        $exists = $this->db->table('position_items')
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->get()
            ->getFirstRow() !== null;

        if ($exists) {
            return;
        }

        $row['slug']   = $slug;
        $row['locale'] = $locale;

        $this->db->table('position_items')->insert($row);
    }
}
