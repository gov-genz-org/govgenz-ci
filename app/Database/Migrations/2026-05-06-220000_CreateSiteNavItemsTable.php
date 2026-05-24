<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSiteNavItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'href_kind' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'href_target' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'match_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
            ],
            'css_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('sort_order');
        $this->forge->createTable('site_nav_items');

        $now = date('Y-m-d H:i:s');

        /* Menu principal = site_govgenz/index.html (nav #nav). Logo → accueil.
         * Accueil, Presse, Rejoindre, Connexion rédaction : hors barre (réactivables en admin). */
        $rows = [
            ['sort_order' => 10, 'label' => 'Qui sommes-nous', 'href_kind' => 'segment', 'href_target' => 'qui-sommes-nous', 'match_key' => 'qui-sommes-nous', 'css_class' => null, 'is_active' => 1],
            ['sort_order' => 20, 'label' => 'Notre ADN', 'href_kind' => 'segment', 'href_target' => 'notre-adn', 'match_key' => 'notre-adn', 'css_class' => null, 'is_active' => 1],
            ['sort_order' => 30, 'label' => 'Structure', 'href_kind' => 'segment', 'href_target' => 'structure', 'match_key' => 'structure', 'css_class' => null, 'is_active' => 1],
            ['sort_order' => 40, 'label' => 'Secteurs', 'href_kind' => 'segment', 'href_target' => 'secteurs', 'match_key' => 'secteurs', 'css_class' => null, 'is_active' => 1],
            ['sort_order' => 50, 'label' => 'Étude', 'href_kind' => 'segment', 'href_target' => 'etude', 'match_key' => 'etude', 'css_class' => null, 'is_active' => 1],
            ['sort_order' => 60, 'label' => 'Contact', 'href_kind' => 'segment', 'href_target' => 'contact', 'match_key' => 'contact', 'css_class' => null, 'is_active' => 1],
            ['sort_order' => 70, 'label' => 'Accueil', 'href_kind' => 'home', 'href_target' => null, 'match_key' => 'home', 'css_class' => null, 'is_active' => 0],
            ['sort_order' => 80, 'label' => 'Presse', 'href_kind' => 'segment', 'href_target' => 'press', 'match_key' => 'press', 'css_class' => null, 'is_active' => 0],
            ['sort_order' => 90, 'label' => 'Rejoindre', 'href_kind' => 'segment', 'href_target' => 'join', 'match_key' => 'join', 'css_class' => null, 'is_active' => 0],
            ['sort_order' => 100, 'label' => 'Connexion rédaction', 'href_kind' => 'path', 'href_target' => 'admin/login', 'match_key' => 'admin_login', 'css_class' => 'ggz-nav-admin', 'is_active' => 0],
        ];

        $batch = [];
        foreach ($rows as $r) {
            $batch[] = array_merge($r, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->db->table('site_nav_items')->insertBatch($batch);
    }

    public function down(): void
    {
        $this->forge->dropTable('site_nav_items', true);
    }
}
