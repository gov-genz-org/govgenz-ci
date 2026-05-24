<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use CodeIgniter\Database\Migration;

/**
 * Aligne le corps HTML des pages marketing sur site_govgenz (sections + en-têtes).
 */
class UpdateMarketingCmsPagesSiteGovgenzBodies extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $map = [
            'qui-sommes-nous' => CmsGovgenzHtmlBodies::quiSommesNous(),
            'notre-adn'       => CmsGovgenzHtmlBodies::notreAdn(),
            'structure'       => CmsGovgenzHtmlBodies::structure(),
            'secteurs'        => CmsGovgenzHtmlBodies::secteurs(),
            'etude'           => CmsGovgenzHtmlBodies::etude(),
            'contact'         => CmsGovgenzHtmlBodies::contact(),
        ];

        foreach ($map as $slug => $html) {
            $row = $this->db->table('cms_pages')->where('slug', $slug)->get()->getRowArray();
            if ($row === null) {
                continue;
            }

            $mode = strtolower(trim((string) ($row['content_mode'] ?? 'html')));
            if ($mode === 'blocks') {
                continue;
            }

            $this->db->table('cms_pages')->where('slug', $slug)->update([
                'body_html'  => $html,
                'layout_key' => 'full',
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Réintroduction manuelle si besoin.
    }
}
