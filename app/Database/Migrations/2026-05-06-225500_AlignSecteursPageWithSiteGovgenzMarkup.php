<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use CodeIgniter\Database\Migration;

/**
 * Remplace le marquage « cartes pêche » par la structure site_govgenz (section--secteurs + grille .sect).
 */
class AlignSecteursPageWithSiteGovgenzMarkup extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $row = $this->db->table('cms_pages')->where('slug', 'secteurs')->get()->getRowArray();
        if ($row === null) {
            return;
        }

        $mode = strtolower(trim((string) ($row['content_mode'] ?? 'html')));
        if ($mode === 'blocks') {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('cms_pages')->where('slug', 'secteurs')->update([
            'body_html'  => CmsGovgenzHtmlBodies::secteurs(),
            'layout_key' => 'full',
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        // Réintroduction manuelle si besoin (contenu éditorial).
    }
}
