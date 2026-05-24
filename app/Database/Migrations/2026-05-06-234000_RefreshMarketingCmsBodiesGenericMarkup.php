<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use CodeIgniter\Database\Migration;

/**
 * Réinjecte les corps HTML marketing (hub, tile-grid, section__source, section__btn-row, section__cta…)
 * pour toutes les pages seedées concernées — pas seulement « etude ».
 */
class RefreshMarketingCmsBodiesGenericMarkup extends Migration
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
        //
    }
}
