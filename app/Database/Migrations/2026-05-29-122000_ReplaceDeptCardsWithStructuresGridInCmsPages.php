<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Remplace les grilles dept_cards statiques par structures_grid sur les pages CMS.
 */
class ReplaceDeptCardsWithStructuresGridInCmsPages extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $rows = $this->db->table('cms_pages')
            ->where('content_mode', 'blocks')
            ->where('body_blocks IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw === '' || $raw === '[]') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                continue;
            }

            $changed = false;
            foreach ($decoded as $idx => $blk) {
                if (! is_array($blk)) {
                    continue;
                }
                if (($blk['type'] ?? '') !== 'cards_grid') {
                    continue;
                }
                if (strtolower(trim((string) ($blk['variant'] ?? ''))) !== 'dept_cards') {
                    continue;
                }
                $decoded[$idx] = ['type' => 'structures_grid'];
                $changed = true;
            }

            if (! $changed) {
                continue;
            }

            $this->db->table('cms_pages')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'body_blocks' => json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
        }
    }

    public function down(): void
    {
    }
}
