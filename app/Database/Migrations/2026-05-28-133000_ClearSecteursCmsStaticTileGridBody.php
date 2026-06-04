<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * La page /secteurs est rendue par le contrôleur dédié + tile_grid BDD.
 * Supprime l’ancien corps CMS statique (tuiles figées sans icônes ni données admin).
 */
class ClearSecteursCmsStaticTileGridBody extends Migration
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

            $bodyHtml = (string) ($row['body_html'] ?? '');
            $rawBlocks = trim((string) ($row['body_blocks'] ?? ''));
            $mode = strtolower(trim((string) ($row['content_mode'] ?? 'html')));

            $isLegacyStaticGrid = str_contains($bodyHtml, 'tile-grid')
                || str_contains($bodyHtml, 'section--secteurs');
            $hasBlocksGrid = $mode === 'blocks'
                && $rawBlocks !== ''
                && $rawBlocks !== '[]'
                && str_contains($rawBlocks, 'sectors_grid');

            if (! $isLegacyStaticGrid && ! $hasBlocksGrid) {
                continue;
            }

            $update = ['updated_at' => $now];
            if ($isLegacyStaticGrid) {
                $update['body_html']    = '';
                $update['content_mode'] = 'html';
                $update['body_blocks']  = null;
            } elseif ($hasBlocksGrid) {
                $update['content_mode'] = 'html';
                $update['body_blocks']  = null;
                if ($bodyHtml === '' || str_contains($bodyHtml, 'tile-grid')) {
                    $update['body_html'] = '';
                }
            }

            $this->db->table('cms_pages')->where('id', (int) ($row['id'] ?? 0))->update($update);
        }
    }

    public function down(): void
    {
    }
}
