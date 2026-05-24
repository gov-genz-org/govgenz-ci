<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzEnglishMarketingSeed;
use CodeIgniter\Database\Migration;

/**
 * Applique les textes anglais (corps + méta + titre) aux pages marketing EN,
 * pour les environnements déjà migrés avec la première version du seed i18n (copie FR).
 */
class RefreshEnglishCmsMarketingBodies extends Migration
{
    /** @var list<string> */
    private const FRENCH_MARKETING_SLUGS = [
        'home',
        'qui-sommes-nous',
        'notre-adn',
        'structure',
        'secteurs',
        'etude',
        'contact',
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        helper(['url']);

        $now = date('Y-m-d H:i:s');

        foreach (self::FRENCH_MARKETING_SLUGS as $slugFr) {
            $enSlug = CmsGovgenzEnglishMarketingSeed::englishSlug($slugFr);
            $body   = CmsGovgenzEnglishMarketingSeed::bodyHtml($slugFr);

            if ($enSlug === null || $body === null) {
                continue;
            }

            $row = $this->db->table('cms_pages')->where('locale', 'en')->where('slug', $enSlug)->get()->getRowArray();

            if ($row === null) {
                continue;
            }

            $meta = CmsGovgenzEnglishMarketingSeed::meta($slugFr);

            $this->db->table('cms_pages')->where('id', (int) ($row['id'] ?? 0))->update([
                'title'            => CmsGovgenzEnglishMarketingSeed::englishTitle($slugFr, (string) ($row['title'] ?? '')),
                'body_html'        => $body,
                'meta_title'       => $meta['meta_title'] ?? ($row['meta_title'] ?? null),
                'meta_description' => $meta['meta_description'] ?? ($row['meta_description'] ?? null),
                'updated_at'       => $now,
            ]);
        }

        if ($this->db->tableExists('site_nav_items') && $this->db->fieldExists('locale', 'site_nav_items')) {
            $this->db->table('site_nav_items')->where('locale', 'en')->where('match_key', 'study')->update([
                'label'      => 'Youth study',
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Pas de restauration fiable du HTML précédent.
    }
}
