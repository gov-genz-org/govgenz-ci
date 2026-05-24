<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use CodeIgniter\Database\Migration;

/**
 * Contact : en-tête rubrique (.section__header) comme les autres pages marketing.
 */
class HarmonizeContactPageSectionHeader extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $slug = 'contact';
        $row  = $this->db->table('cms_pages')->where('slug', $slug)->get()->getRowArray();
        if ($row === null) {
            return;
        }

        if (strtolower(trim((string) ($row['content_mode'] ?? 'html'))) === 'blocks') {
            return;
        }

        $this->db->table('cms_pages')->where('slug', $slug)->update([
            'body_html'  => CmsGovgenzHtmlBodies::contact(),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down(): void
    {
        //
    }
}
