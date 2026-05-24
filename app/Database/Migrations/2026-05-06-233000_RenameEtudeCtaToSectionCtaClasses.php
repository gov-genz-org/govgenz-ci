<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use CodeIgniter\Database\Migration;

/**
 * Ancienne étape « corps étude » seule ; les nouvelles classes génériques sont appliquées partout via
 * 2026-05-06-234000_RefreshMarketingCmsBodiesGenericMarkup (réinjecte tous les corps marketing).
 */
class RenameEtudeCtaToSectionCtaClasses extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $slug = 'etude';
        $row  = $this->db->table('cms_pages')->where('slug', $slug)->get()->getRowArray();
        if ($row === null) {
            return;
        }

        if (strtolower(trim((string) ($row['content_mode'] ?? 'html'))) === 'blocks') {
            return;
        }

        $this->db->table('cms_pages')->where('slug', $slug)->update([
            'body_html'  => CmsGovgenzHtmlBodies::etude(),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down(): void
    {
        //
    }
}
