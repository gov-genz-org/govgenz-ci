<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Aligne le menu principal sur site_govgenz/index.html :
 * Qui sommes-nous · Notre ADN · Structure · Secteurs · Étude · Contact
 * (pas d’« Accueil » dans la nav — le logo renvoie à l’accueil ; Presse / Rejoindre hors barre).
 */
class AlignPublicNavWithSiteGovgenz extends Migration
{
    /** @var array<string, array{sort_order: int, is_active: int}> */
    private const ROWS = [
        'qui-sommes-nous' => ['sort_order' => 10, 'is_active' => 1],
        'notre-adn'       => ['sort_order' => 20, 'is_active' => 1],
        'structure'       => ['sort_order' => 30, 'is_active' => 1],
        'secteurs'        => ['sort_order' => 40, 'is_active' => 1],
        'etude'           => ['sort_order' => 50, 'is_active' => 1],
        'contact'         => ['sort_order' => 60, 'is_active' => 1],
        'home'            => ['sort_order' => 70, 'is_active' => 0],
        'press'           => ['sort_order' => 80, 'is_active' => 0],
        'join'            => ['sort_order' => 90, 'is_active' => 0],
        'admin_login'     => ['sort_order' => 100, 'is_active' => 0],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (self::ROWS as $matchKey => $cfg) {
            $this->db->table('site_nav_items')
                ->where('match_key', $matchKey)
                ->update([
                    'sort_order' => $cfg['sort_order'],
                    'is_active'  => $cfg['is_active'],
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('site_nav_items')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        /* État proche du premier jeu « menu complet » (avant alignement maquette) */
        $restore = [
            'home'            => ['sort_order' => 10, 'is_active' => 1],
            'qui-sommes-nous' => ['sort_order' => 20, 'is_active' => 1],
            'notre-adn'       => ['sort_order' => 30, 'is_active' => 1],
            'structure'       => ['sort_order' => 40, 'is_active' => 1],
            'secteurs'        => ['sort_order' => 50, 'is_active' => 1],
            'etude'           => ['sort_order' => 60, 'is_active' => 1],
            'press'           => ['sort_order' => 70, 'is_active' => 1],
            'contact'         => ['sort_order' => 80, 'is_active' => 1],
            'join'            => ['sort_order' => 90, 'is_active' => 1],
            'admin_login'     => ['sort_order' => 100, 'is_active' => 1],
        ];

        foreach ($restore as $matchKey => $cfg) {
            $this->db->table('site_nav_items')
                ->where('match_key', $matchKey)
                ->update([
                    'sort_order' => $cfg['sort_order'],
                    'is_active'  => $cfg['is_active'],
                    'updated_at' => $now,
                ]);
        }
    }
}
