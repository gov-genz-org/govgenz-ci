<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menu principal : Qui sommes-nous · Structure · Secteurs · Nos positions · Nos projets · Contact (à droite).
 * Masque Notre ADN et Étude.
 */
class RealignPublicMainNavProgramAndContact extends Migration
{
    /** @var list<string> */
    private const HIDDEN_MATCH_KEYS = [
        'notre-adn',
        'our-dna',
        'etude',
        'study',
        'projets-programme',
        'projects-program',
        'projets',
    ];

    /** @var array<string, array{sort_order: int, label?: string, href_kind?: string, href_target?: string, css_class?: string|null, is_active?: int}> */
    private const FR_UPDATES = [
        'qui-sommes-nous' => ['sort_order' => 10],
        'structure'       => ['sort_order' => 20],
        'secteurs'        => ['sort_order' => 30],
        'positions'       => ['sort_order' => 40, 'label' => 'Nos positions', 'href_kind' => 'path', 'href_target' => 'positions', 'is_active' => 1],
        'projects'        => ['sort_order' => 50, 'label' => 'Nos projets', 'href_kind' => 'path', 'href_target' => 'projects', 'is_active' => 1],
        'contact'         => ['sort_order' => 90, 'css_class' => 'ggz-nav-end', 'is_active' => 1],
    ];

    /** @var array<string, array{sort_order: int, label?: string, href_kind?: string, href_target?: string, css_class?: string|null, is_active?: int}> */
    private const EN_UPDATES = [
        'qui-sommes-nous' => ['sort_order' => 10],
        'who-we-are'      => ['sort_order' => 10],
        'structure'       => ['sort_order' => 20],
        'sectors'         => ['sort_order' => 30],
        'secteurs'        => ['sort_order' => 30],
        'positions'       => ['sort_order' => 40, 'label' => 'Our positions', 'href_kind' => 'path', 'href_target' => 'positions', 'is_active' => 1],
        'projects'        => ['sort_order' => 50, 'label' => 'Our projects', 'href_kind' => 'path', 'href_target' => 'projects', 'is_active' => 1],
        'contact'         => ['sort_order' => 90, 'css_class' => 'ggz-nav-end', 'is_active' => 1],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->hideLegacyItems($now);
        $this->deactivateDuplicateProjectNav($now);
        $this->ensureProgramNavItems($now);
        $this->applyLocaleUpdates('fr', self::FR_UPDATES, $now);
        $this->applyLocaleUpdates('en', self::EN_UPDATES, $now);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (['fr', 'en'] as $locale) {
            foreach (self::HIDDEN_MATCH_KEYS as $mk) {
                $this->updateNavRow($locale, $mk, ['is_active' => 1], $now);
            }
        }

        $restore = [
            'qui-sommes-nous' => 10,
            'notre-adn'         => 20,
            'structure'         => 30,
            'secteurs'          => 40,
            'etude'             => 50,
            'contact'           => 60,
            'positions'         => 55,
            'projects'          => 56,
        ];

        foreach (['fr', 'en'] as $locale) {
            foreach ($restore as $mk => $sort) {
                $this->updateNavRow($locale, $mk, [
                    'sort_order' => $sort,
                    'css_class'  => null,
                ], $now);
            }
            $this->updateNavRow($locale, 'contact', ['css_class' => null], $now);
        }

        $builder = $this->db->table('site_nav_items')->where('match_key', 'projects');
        if ($this->db->fieldExists('locale', 'site_nav_items')) {
            $builder->whereIn('locale', ['fr', 'en']);
        }
        $builder->delete();
    }

    private function hideLegacyItems(string $now): void
    {
        $builder = $this->db->table('site_nav_items')->whereIn('match_key', self::HIDDEN_MATCH_KEYS);
        $builder->update([
            'is_active'  => 0,
            'updated_at' => $now,
        ]);
    }

    private function deactivateDuplicateProjectNav(string $now): void
    {
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
    }

    private function ensureProgramNavItems(string $now): void
    {
        $this->insertNavIfMissing('fr', [
            'sort_order'  => 40,
            'label'       => 'Nos positions',
            'href_kind'   => 'path',
            'href_target' => 'positions',
            'match_key'   => 'positions',
            'css_class'   => null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $this->insertNavIfMissing('en', [
            'sort_order'  => 40,
            'label'       => 'Our positions',
            'href_kind'   => 'path',
            'href_target' => 'positions',
            'match_key'   => 'positions',
            'css_class'   => null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $this->insertNavIfMissing('fr', [
            'sort_order'  => 50,
            'label'       => 'Nos projets',
            'href_kind'   => 'path',
            'href_target' => 'projects',
            'match_key'   => 'projects',
            'css_class'   => null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $this->insertNavIfMissing('en', [
            'sort_order'  => 50,
            'label'       => 'Our projects',
            'href_kind'   => 'path',
            'href_target' => 'projects',
            'match_key'   => 'projects',
            'css_class'   => null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    /**
     * @param array<string, array{sort_order: int, label?: string, href_kind?: string, href_target?: string, css_class?: string|null, is_active?: int}> $updates
     */
    private function applyLocaleUpdates(string $locale, array $updates, string $now): void
    {
        foreach ($updates as $matchKey => $cfg) {
            $this->updateNavRow($locale, $matchKey, $cfg, $now);
        }
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

    /**
     * @param array{sort_order?: int, label?: string, href_kind?: string, href_target?: string, css_class?: string|null, is_active?: int} $changes
     */
    private function updateNavRow(string $locale, string $matchKey, array $changes, string $now): void
    {
        $builder = $this->db->table('site_nav_items')->where('match_key', $matchKey);

        if ($this->db->fieldExists('locale', 'site_nav_items')) {
            $builder->where('locale', $locale);
        }

        $payload = ['updated_at' => $now];
        foreach (['sort_order', 'label', 'href_kind', 'href_target', 'css_class', 'is_active'] as $field) {
            if (array_key_exists($field, $changes)) {
                $payload[$field] = $changes[$field];
            }
        }

        $builder->update($payload);
    }
}
