<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzEnglishMarketingSeed;
use CodeIgniter\Database\Migration;

/**
 * Contenus FR (URLs sans préfixe) et EN sous /en/… : locale + groupe de traduction.
 */
class AddLocaleToCmsPagesPostsAndNav extends Migration
{
    public function up(): void
    {
        // --- cms_pages ---
        if ($this->db->tableExists('cms_pages')) {
            if (! $this->db->fieldExists('locale', 'cms_pages')) {
                $this->dropUniqueIndexOnColumnOnly('cms_pages', 'slug');
                $this->forge->addColumn('cms_pages', [
                    'locale'            => ['type' => 'VARCHAR', 'constraint' => 8, 'default' => 'fr', 'after' => 'slug'],
                    'translation_group' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'locale'],
                ]);
            } elseif (! $this->db->fieldExists('translation_group', 'cms_pages')) {
                $this->forge->addColumn('cms_pages', [
                    'translation_group' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'locale'],
                ]);
            }

            $this->db->query('UPDATE cms_pages SET translation_group = CAST(id AS CHAR) WHERE translation_group IS NULL');

            if (! $this->indexExists('cms_pages', 'cms_pages_slug_locale')) {
                $this->dropUniqueIndexOnColumnOnly('cms_pages', 'slug');
                $this->forge->addKey(['slug', 'locale'], false, true, 'cms_pages_slug_locale');
            }

            $this->seedEnglishPagesIfNeeded();
        }

        // --- cms_posts ---
        if ($this->db->tableExists('cms_posts')) {
            if (! $this->db->fieldExists('locale', 'cms_posts')) {
                $this->dropUniqueIndexOnColumnOnly('cms_posts', 'slug');
                $this->forge->addColumn('cms_posts', [
                    'locale'            => ['type' => 'VARCHAR', 'constraint' => 8, 'default' => 'fr', 'after' => 'slug'],
                    'translation_group' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'locale'],
                ]);
            } elseif (! $this->db->fieldExists('translation_group', 'cms_posts')) {
                $this->forge->addColumn('cms_posts', [
                    'translation_group' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'locale'],
                ]);
            }

            $this->db->query('UPDATE cms_posts SET translation_group = CAST(id AS CHAR) WHERE translation_group IS NULL');

            if (! $this->indexExists('cms_posts', 'cms_posts_slug_locale')) {
                $this->dropUniqueIndexOnColumnOnly('cms_posts', 'slug');
                $this->forge->addKey(['slug', 'locale'], false, true, 'cms_posts_slug_locale');
            }

            $this->seedEnglishPostsIfNeeded();
        }

        // --- site_nav_items ---
        if ($this->db->tableExists('site_nav_items')) {
            if (! $this->db->fieldExists('locale', 'site_nav_items')) {
                $this->forge->addColumn('site_nav_items', [
                    'locale' => ['type' => 'VARCHAR', 'constraint' => 8, 'default' => 'fr', 'after' => 'id'],
                ]);
            }

            $this->seedEnglishNavIfNeeded();
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('cms_pages')) {
            $this->forge->dropKey('cms_pages', 'cms_pages_slug_locale');
            if ($this->db->fieldExists('locale', 'cms_pages')) {
                $this->forge->dropColumn('cms_pages', ['locale', 'translation_group']);
            }
            $this->forge->addKey('slug', false, true);
        }

        if ($this->db->tableExists('cms_posts')) {
            $this->forge->dropKey('cms_posts', 'cms_posts_slug_locale');
            if ($this->db->fieldExists('locale', 'cms_posts')) {
                $this->forge->dropColumn('cms_posts', ['locale', 'translation_group']);
            }
            $this->forge->addKey('slug', false, true);
        }

        if ($this->db->tableExists('site_nav_items') && $this->db->fieldExists('locale', 'site_nav_items')) {
            $this->db->table('site_nav_items')->where('locale', 'en')->delete();
            $this->forge->dropColumn('site_nav_items', 'locale');
        }
    }

    private function indexExists(string $table, string $keyName): bool
    {
        if (! preg_match('/^[a-z0-9_]+$/', $table)) {
            return false;
        }

        $rows = $this->db->query(
            'SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?',
            [$keyName],
        )->getResultArray();

        return $rows !== [];
    }

    /**
     * Ne conserve que les colonnes présentes sur la table (schémas locaux variables).
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function insertOnlyExistingColumns(string $table, array $data): array
    {
        $out = [];
        foreach ($data as $column => $value) {
            if ($this->db->fieldExists((string) $column, $table)) {
                $out[(string) $column] = $value;
            }
        }

        return $out;
    }

    /**
     * Supprime un index UNIQUE dont la seule colonne est $column (ex. slug seul).
     */
    private function dropUniqueIndexOnColumnOnly(string $table, string $column): void
    {
        $rows = $this->db->query('SHOW INDEX FROM `' . $table . '`')->getResultArray();

        /** @var array<string, list<string>> $colsByKey */
        $colsByKey = [];

        foreach ($rows as $row) {
            $key = (string) ($row['Key_name'] ?? '');
            if ($key === '' || $key === 'PRIMARY') {
                continue;
            }

            $nonUnique = (int) ($row['Non_unique'] ?? 1);
            if ($nonUnique !== 0) {
                continue;
            }

            $col = (string) ($row['Column_name'] ?? '');
            $seq = (int) ($row['Seq_in_index'] ?? 1);
            $colsByKey[$key][$seq] = $col;
        }

        foreach ($colsByKey as $keyName => $seqCols) {
            ksort($seqCols);
            $cols = array_values($seqCols);
            if ($cols === [$column]) {
                $this->db->query('ALTER TABLE `' . $table . '` DROP INDEX `' . $keyName . '`');

                return;
            }
        }
    }

    private function seedEnglishNavIfNeeded(): void
    {
        $db = $this->db;

        if ($db->table('site_nav_items')->where('locale', 'en')->countAllResults() > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $rows = $db->table('site_nav_items')->where('locale', 'fr')->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray();

        foreach ($rows as $r) {
            $mapped       = $this->navEnglishFromRow($r);
            $hrefKind     = strtolower(trim((string) ($r['href_kind'] ?? '')));
            $hrefTargetFr = trim((string) ($r['href_target'] ?? ''));

            $db->table('site_nav_items')->insert([
                'locale'       => 'en',
                'sort_order'   => (int) ($r['sort_order'] ?? 0),
                'label'        => $mapped['label'],
                'href_kind'    => $hrefKind !== '' ? $hrefKind : 'segment',
                'href_target'  => $mapped['href_target'] !== '' ? $mapped['href_target'] : ($hrefTargetFr !== '' ? $hrefTargetFr : null),
                'match_key'    => $mapped['match_key'],
                'css_class'    => $r['css_class'] ?? null,
                'is_active'    => (int) ($r['is_active'] ?? 1),
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{label: string, href_target: string, match_key: string}
     */
    private function navEnglishFromRow(array $row): array
    {
        $kind   = strtolower(trim((string) ($row['href_kind'] ?? '')));
        $target = trim((string) ($row['href_target'] ?? ''));
        $mk     = trim((string) ($row['match_key'] ?? ''));

        if ($kind === 'home') {
            return ['label' => 'Home', 'href_target' => '', 'match_key' => 'home'];
        }

        if ($kind === 'path' && str_starts_with($target, 'admin')) {
            return ['label' => 'Staff login', 'href_target' => $target, 'match_key' => $mk !== '' ? $mk : 'admin_login'];
        }

        $segmentMap = [
            'qui-sommes-nous' => ['Who we are', 'who-we-are', 'who-we-are'],
            'notre-adn'       => ['Our DNA', 'our-dna', 'our-dna'],
            'structure'       => ['Structure', 'structure', 'structure'],
            'secteurs'        => ['Sectors', 'sectors', 'sectors'],
            'etude'           => ['Youth study', 'study', 'study'],
            'contact'         => ['Contact', 'contact', 'contact'],
            'presse'          => ['Press', 'press', 'press'],
            'rejoignez-nous'  => ['Join us', 'join', 'join'],
            'press'           => ['Press', 'press', 'press'],
            'join'            => ['Join us', 'join', 'join'],
        ];

        $key = $target !== '' ? $target : $mk;

        if (isset($segmentMap[$key])) {
            [$label, $href, $match] = $segmentMap[$key];

            return ['label' => $label, 'href_target' => $href, 'match_key' => $match];
        }

        $labelEn = (string) ($row['label'] ?? $key);

        return ['label' => $labelEn, 'href_target' => $target, 'match_key' => $mk !== '' ? $mk : $target];
    }

    private function seedEnglishPagesIfNeeded(): void
    {
        $db = $this->db;

        if ($db->table('cms_pages')->where('locale', 'en')->countAllResults() > 0) {
            return;
        }

        $pages = $db->table('cms_pages')->where('locale', 'fr')->get()->getResultArray();

        foreach ($pages as $p) {
            $slugFr = (string) ($p['slug'] ?? '');
            $enSlug = CmsGovgenzEnglishMarketingSeed::englishSlug($slugFr);

            if ($enSlug === null) {
                continue;
            }

            $tg     = (string) ($p['translation_group'] ?? $p['id']);
            $metaEn = CmsGovgenzEnglishMarketingSeed::meta($slugFr);

            $insert = [
                'slug'              => $enSlug,
                'locale'            => 'en',
                'translation_group' => $tg,
                'title'             => CmsGovgenzEnglishMarketingSeed::englishTitle($slugFr, (string) ($p['title'] ?? '')),
                'meta_description'  => $metaEn['meta_description'] ?? ($p['meta_description'] ?? null),
                'body_html'         => CmsGovgenzEnglishMarketingSeed::bodyHtml($slugFr) ?? ($p['body_html'] ?? null),
                'status'            => $p['status'] ?? 'published',
                'created_at'        => $p['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at'        => $p['updated_at'] ?? date('Y-m-d H:i:s'),
            ];

            if (isset($metaEn['meta_title'])) {
                $insert['meta_title'] = $metaEn['meta_title'];
            } elseif (array_key_exists('meta_title', $p)) {
                $insert['meta_title'] = $p['meta_title'];
            }

            foreach (['layout_key', 'content_mode', 'body_blocks'] as $optional) {
                if (array_key_exists($optional, $p)) {
                    $insert[$optional] = $p[$optional];
                }
            }

            $db->table('cms_pages')->insert($this->insertOnlyExistingColumns('cms_pages', $insert));
        }
    }

    private function seedEnglishPostsIfNeeded(): void
    {
        $db = $this->db;

        if ($db->table('cms_posts')->where('locale', 'en')->countAllResults() > 0) {
            return;
        }

        $posts = $db->table('cms_posts')->where('locale', 'fr')->get()->getResultArray();

        foreach ($posts as $post) {
            $slug = (string) ($post['slug'] ?? '');

            if ($slug === '') {
                continue;
            }

            $tg = (string) ($post['translation_group'] ?? $post['id']);

            $insert = [
                'slug'              => $slug,
                'locale'            => 'en',
                'translation_group' => $tg,
                'title'             => ((string) ($post['title'] ?? '')) !== ''
                    ? (string) $post['title']
                    : 'Post',
                'excerpt'           => $post['excerpt'] ?? null,
                'body_html'         => $post['body_html'] ?? null,
                'status'            => $post['status'] ?? 'published',
                'published_at'      => $post['published_at'] ?? null,
                'meta_title'       => $post['meta_title'] ?? null,
                'meta_description' => $post['meta_description'] ?? null,
                'created_at'        => $post['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at'        => $post['updated_at'] ?? date('Y-m-d H:i:s'),
            ];

            foreach (['featured_image_url', 'content_mode', 'body_blocks'] as $optional) {
                if (array_key_exists($optional, $post)) {
                    $insert[$optional] = $post[$optional];
                }
            }

            $db->table('cms_posts')->insert($this->insertOnlyExistingColumns('cms_posts', $insert));
        }
    }
}
