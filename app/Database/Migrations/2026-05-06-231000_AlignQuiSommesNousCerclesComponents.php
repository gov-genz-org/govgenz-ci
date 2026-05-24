<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use CodeIgniter\Database\Migration;

/**
 * Remplace la grille « ggz-stat-card » par les composants template (.cercles / .cercle).
 */
class AlignQuiSommesNousCerclesComponents extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $slug = 'qui-sommes-nous';
        $row  = $this->db->table('cms_pages')->where('slug', $slug)->get()->getRowArray();
        if ($row === null) {
            return;
        }

        if (strtolower(trim((string) ($row['content_mode'] ?? 'html'))) === 'blocks') {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('cms_pages')->where('slug', $slug)->update([
            'body_html'  => CmsGovgenzHtmlBodies::quiSommesNous(),
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        //
    }
}
