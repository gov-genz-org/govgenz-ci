<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Aligne les translation_group FR/EN pour le switch de langue sur les fiches détail.
 */
class FixDeclarationItemTranslationGroups extends Migration
{
    /** @var array<string, array{fr: string, en: string}> */
    private const PAIRS = [
        'decl-youth-decisions' => [
            'fr' => 'plaidoyer-jeunesse-decisions',
            'en' => 'youth-public-decisions-advocacy',
        ],
        'decl-principles-2026' => [
            'fr' => 'declaration-principes-2026',
            'en' => 'principles-declaration-2026',
        ],
        'decl-mining-alert' => [
            'fr' => 'alerte-contrats-miniers',
            'en' => 'alert-opaque-mining',
        ],
        'decl-partnership-ptf' => [
            'fr' => 'partenariat-ptf',
            'en' => 'partnership-donors',
        ],
        'decl-citizen-alliance' => [
            'fr' => 'alliance-osc',
            'en' => 'citizen-alliance-call',
        ],
        'decl-inst-partnership' => [
            'fr' => 'partenariat-institutionnel',
            'en' => 'institutional-partnership',
        ],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (self::PAIRS as $group => $slugs) {
            foreach (['fr', 'en'] as $locale) {
                $slug = $slugs[$locale];
                $this->db->table('declaration_items')
                    ->where('slug', $slug)
                    ->where('locale', $locale)
                    ->update([
                        'translation_group' => $group,
                        'updated_at'        => $now,
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        $legacy = [
            'decl-youth-decisions'    => ['fr' => 'plaidoyer-jeunesse', 'en' => 'youth-decisions'],
            'decl-principles-2026'    => ['fr' => 'declaration-principes', 'en' => 'principles-decl'],
            'decl-mining-alert'       => ['fr' => 'alerte-miniers', 'en' => 'alert-mining'],
            'decl-partnership-ptf'    => ['fr' => 'ptf', 'en' => 'donors'],
            'decl-citizen-alliance'   => ['fr' => 'alliance-osc', 'en' => 'citizen-alliance'],
            'decl-inst-partnership'   => ['fr' => 'partenariat-inst', 'en' => 'inst-partnership'],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($legacy as $group => $slugs) {
            foreach (['fr', 'en'] as $locale) {
                $this->db->table('declaration_items')
                    ->where('slug', $slugs[$locale])
                    ->where('locale', $locale)
                    ->where('translation_group', $group)
                    ->update([
                        'translation_group' => $slugs[$locale],
                        'updated_at'        => $now,
                    ]);
            }
        }
    }
}
