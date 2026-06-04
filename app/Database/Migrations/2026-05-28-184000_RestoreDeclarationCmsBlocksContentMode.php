<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Page declaration : mode blocs si du contenu structuré est présent (après phase « bandeau seul »).
 */
class RestoreDeclarationCmsBlocksContentMode extends Migration
{
    private const TRANSLATION_GROUP = 'declaration-program-page';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $rows = $this->db->table('cms_pages')
            ->where('slug', 'declaration')
            ->where('translation_group', self::TRANSLATION_GROUP)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw === '' || $raw === '[]' || $raw === 'null') {
                continue;
            }

            if (strtolower(trim((string) ($row['content_mode'] ?? ''))) === 'blocks') {
                continue;
            }

            $this->db->table('cms_pages')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'content_mode' => 'blocks',
                    'body_html'    => '',
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
        }
    }

    public function down(): void
    {
    }
}
