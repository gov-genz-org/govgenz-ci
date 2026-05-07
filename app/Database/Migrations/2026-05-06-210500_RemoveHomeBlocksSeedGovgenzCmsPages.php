<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use CodeIgniter\Database\Migration;

/**
 * Retire cms_home_blocks au profit de cms_pages (édition via Administration → Pages).
 */
class RemoveHomeBlocksSeedGovgenzCmsPages extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('cms_home_blocks')) {
            $this->forge->dropTable('cms_home_blocks', true);
        }

        $this->ensureCmsPagesTable();

        helper('url');

        $now = date('Y-m-d H:i:s');

        foreach ($this->pageRows($now) as $row) {
            $slug = (string) ($row['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $exists = $this->db->table('cms_pages')->where('slug', $slug)->get()->getFirstRow() !== null;
            if ($exists) {
                continue;
            }
            $this->db->table('cms_pages')->insert($row);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('cms_home_blocks')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'section' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'eyebrow' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'subtitle' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'body_html' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'link_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'link_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'payload_json' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('section');
        $this->forge->addKey(['section', 'sort_order']);
        $this->forge->createTable('cms_home_blocks');
    }

    private function ensureCmsPagesTable(): void
    {
        if ($this->db->tableExists('cms_pages')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'body_html' => [
                'type' => 'MEDIUMTEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'draft',
            ],
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'meta_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'layout_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('slug', false, true);
        $this->forge->createTable('cms_pages');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pageRows(string $now): array
    {
        return [
            [
                'slug'             => 'home',
                'title'            => 'GoV Gen Z Madagascar',
                'body_html'        => $this->buildBodyHome(),
                'status'           => 'published',
                'meta_title'       => 'GoV Gen Z Madagascar',
                'meta_description' => 'Programme Paikady Taninjanaka — mouvement citoyen pour la jeunesse et l’avenir de Madagascar.',
                'layout_key'       => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'qui-sommes-nous',
                'title'            => 'Qui sommes-nous',
                'body_html'        => CmsGovgenzHtmlBodies::quiSommesNous(),
                'status'           => 'published',
                'meta_title'       => 'Qui sommes-nous · GoV Gen Z Madagascar',
                'meta_description' => 'Pour eux, avec vous — cinq cercles concentriques pour construire l’avenir.',
                'layout_key'       => 'full',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'notre-adn',
                'title'            => 'Notre ADN',
                'body_html'        => CmsGovgenzHtmlBodies::notreAdn(),
                'status'           => 'published',
                'meta_title'       => 'L’ADN de GoV Gen Z Madagascar',
                'meta_description' => 'Pour qui, nos valeurs, notre méthode et notre but.',
                'layout_key'       => 'full',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'structure',
                'title'            => 'Structure',
                'body_html'        => CmsGovgenzHtmlBodies::structure(),
                'status'           => 'published',
                'meta_title'       => 'Structure · GoV Gen Z Madagascar',
                'meta_description' => 'Organisation transparente : noyau exécutif et fonctions transversales.',
                'layout_key'       => 'full',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'secteurs',
                'title'            => 'Secteurs',
                'body_html'        => CmsGovgenzHtmlBodies::secteurs(),
                'status'           => 'published',
                'meta_title'       => 'Équipes sectorielles · GoV Gen Z Madagascar',
                'meta_description' => 'Quatorze domaines d’action — contact direct par équipe.',
                'layout_key'       => 'full',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'etude',
                'title'            => 'Étude jeunesse',
                'body_html'        => CmsGovgenzHtmlBodies::etude(),
                'status'           => 'published',
                'meta_title'       => 'Étude jeunesse 2026 · GoV Gen Z Madagascar',
                'meta_description' => 'Les chiffres qui portent le mouvement — base indicative pour l’action.',
                'layout_key'       => 'full',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'contact',
                'title'            => 'Contact',
                'body_html'        => CmsGovgenzHtmlBodies::contact(),
                'status'           => 'published',
                'meta_title'       => 'Contact · GoV Gen Z Madagascar',
                'meta_description' => 'Portes d’entrée — contact général, rejoindre, partenariat, presse.',
                'layout_key'       => 'full',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];
    }

    private function buildBodyHome(): string
    {
        $qui    = site_url('qui-sommes-nous');
        $contact = site_url('contact');
        $join   = site_url('join');

        return <<<HTML
<section class="ggz-page-hero ggz-home-section" id="accueil" aria-labelledby="hero-heading">
    <span class="ggz-eyebrow">Programme Paikady Taninjanaka</span>
    <h1 id="hero-heading">GoV Gen Z Madagascar</h1>
    <p class="ggz-lead">Mouvement structuré pour bâtir un avenir digne, serein et durable — dignité et sérénité pour le peuple, un avenir meilleur pour la jeunesse et les générations futures.</p>
    <div class="ggz-actions">
        <a class="btn btn-primary" href="{$qui}">Découvrir le mouvement</a>
        <a class="btn btn-secondary" href="{$contact}">Nous écrire</a>
        <a class="btn btn-secondary" href="{$join}">Rejoindre</a>
    </div>
    <ul class="ggz-trust-list">
        <li>Noyau exécutif &amp; coordination</li>
        <li>14 équipes sectorielles</li>
        <li>Impacts mesurables</li>
    </ul>
</section>
<p class="muted">Le détail des rubriques ci-dessous est éditable dans <strong>Administration → Pages</strong> (slug : <code>qui-sommes-nous</code>, <code>notre-adn</code>, etc.).</p>
HTML;
    }
}
