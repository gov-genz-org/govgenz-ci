<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Page /secteurs : bloc sectors_grid pour choisir compact vs wide dans l’admin Pages.
 */
class SeedSecteursPageSectorsGridLayoutBlock extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach (['secteurs', 'sectors'] as $slug) {
            $row = $this->db->table('cms_pages')->where('slug', $slug)->get()->getRowArray();
            if ($row === null) {
                continue;
            }

            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw !== '' && $raw !== '[]' && str_contains($raw, 'sectors_grid')) {
                continue;
            }

            $blocks = [['type' => 'sectors_grid', 'layout' => 'wide']];

            $this->db->table('cms_pages')->where('id', (int) ($row['id'] ?? 0))->update([
                'content_mode' => 'blocks',
                'body_html'    => '',
                'body_blocks'  => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                'updated_at'   => $now,
            ]);

            \App\Libraries\CmsPublishedPageCache::forget((string) ($row['locale'] ?? 'fr'), $slug);
        }
    }

    public function down(): void
    {
    }
}
