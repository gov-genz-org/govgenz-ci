<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Une seule entrée menu « Nos projets » : match_key projects → /projects.
 * Désactive les doublons (projets-programme, projects-program, 2e projects, etc.).
 */
class DeduplicateProjectsNavMenuItems extends Migration
{
    /** @var list<string> */
    private const LEGACY_PROJECT_MATCH_KEYS = [
        'projets-programme',
        'projects-program',
        'projets',
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('site_nav_items')
            ->whereIn('match_key', self::LEGACY_PROJECT_MATCH_KEYS)
            ->update([
                'is_active'  => 0,
                'updated_at' => $now,
            ]);

        $this->db->table('site_nav_items')
            ->where('is_active', 1)
            ->groupStart()
                ->where('href_target', 'projects')
                ->orWhere('href_target', 'projets')
            ->groupEnd()
            ->whereNotIn('match_key', ['projects'])
            ->update([
                'is_active'  => 0,
                'updated_at' => $now,
            ]);

        $locales = $this->db->fieldExists('locale', 'site_nav_items') ? ['fr', 'en'] : [null];

        foreach ($locales as $locale) {
            $this->deactivateExtraProjectsRows($locale, $now);
            $this->ensureCanonicalProjectsRow($locale, $now);
        }
    }

    public function down(): void
    {
        // Pas de restauration des doublons.
    }

    private function deactivateExtraProjectsRows(?string $locale, string $now): void
    {
        $builder = $this->db->table('site_nav_items')
            ->where('match_key', 'projects')
            ->orderBy('id', 'ASC');

        if ($locale !== null) {
            $builder->where('locale', $locale);
        }

        $rows = $builder->get()->getResultArray();

        if (count($rows) <= 1) {
            return;
        }

        foreach (array_slice($rows, 1) as $dup) {
            $this->db->table('site_nav_items')
                ->where('id', (int) ($dup['id'] ?? 0))
                ->update(['is_active' => 0, 'updated_at' => $now]);
        }
    }

    private function ensureCanonicalProjectsRow(?string $locale, string $now): void
    {
        $label = ($locale === 'en') ? 'Our projects' : 'Nos projets';

        $builder = $this->db->table('site_nav_items')->where('match_key', 'projects');

        if ($locale !== null) {
            $builder->where('locale', $locale);
        }

        $row = $builder->orderBy('id', 'ASC')->get()->getFirstRow('array');

        if ($row === null) {
            $insert = [
                'sort_order'  => 50,
                'label'       => $label,
                'href_kind'   => 'path',
                'href_target' => 'projects',
                'match_key'   => 'projects',
                'css_class'   => null,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
            if ($locale !== null) {
                $insert['locale'] = $locale;
            }
            $this->db->table('site_nav_items')->insert($insert);

            return;
        }

        $this->db->table('site_nav_items')
            ->where('id', (int) ($row['id'] ?? 0))
            ->update([
                'label'       => $label,
                'href_kind'   => 'path',
                'href_target' => 'projects',
                'sort_order'  => 50,
                'is_active'   => 1,
                'updated_at'  => $now,
            ]);
    }
}
