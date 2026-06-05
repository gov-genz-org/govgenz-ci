<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Corrige le bandeau CMS /en/press lorsque la ligne EN a conservé les textes FR par erreur.
 */
class FixCmsPressEnListPageHero extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $row = $this->db->table('cms_pages')
            ->where('slug', 'press')
            ->where('locale', 'en')
            ->get()
            ->getRowArray();

        if ($row === null) {
            return;
        }

        $heroTitle = trim((string) ($row['hero_title'] ?? ''));
        $metaTitle = trim((string) ($row['meta_title'] ?? ''));
        if ($heroTitle !== 'Presse' && ! str_starts_with($metaTitle, 'Presse')) {
            return;
        }

        $this->db->table('cms_pages')
            ->where('id', (int) ($row['id'] ?? 0))
            ->update([
                'title'            => 'Press listing',
                'hero_overline'    => 'MEDIA',
                'hero_title'       => 'Press',
                'hero_lead'        => 'Statements and news published by GovGenZ Madagascar.',
                'meta_title'       => 'Press — GovGenZ',
                'meta_description' => 'Press releases and news published by GovGenZ Madagascar.',
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

        CmsPublishedPageCache::forget('en', 'press');
    }

    public function down(): void
    {
        // Données éditoriales — pas de retour arrière automatique.
    }
}
