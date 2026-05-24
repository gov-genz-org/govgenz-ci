<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Entrée menu public « Nos positions » / « Our positions » → /positions (liste programme).
 */
class AddPositionsToSiteNavItems extends Migration
{
    private const MATCH_KEY = 'positions';

    public function up(): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->insertNavIfMissing('fr', [
            'sort_order'  => 40,
            'label'       => 'Nos positions',
            'href_kind'   => 'path',
            'href_target' => 'positions',
            'match_key'   => self::MATCH_KEY,
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
            'match_key'   => self::MATCH_KEY,
            'css_class'   => null,
            'is_active'   => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $builder = $this->db->table('site_nav_items')->where('match_key', self::MATCH_KEY);

        if ($this->db->fieldExists('locale', 'site_nav_items')) {
            $builder->whereIn('locale', ['fr', 'en']);
        }

        $builder->delete();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertNavIfMissing(string $locale, array $row): void
    {
        $builder = $this->db->table('site_nav_items')->where('match_key', self::MATCH_KEY);

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
