<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Page CMS declaration = bandeau liste uniquement (comme projects / positions).
 */
class ClearDeclarationCmsBodyForListHeroOnly extends Migration
{
    private const TRANSLATION_GROUP = 'declaration-program-page';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $this->db->table('cms_pages')
            ->where('slug', 'declaration')
            ->where('translation_group', self::TRANSLATION_GROUP)
            ->update([
                'content_mode' => 'html',
                'body_html'    => '',
                'body_blocks'  => null,
                'layout_key'   => 'full',
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
    }

    public function down(): void
    {
        // Corps statique volontairement non restauré.
    }
}
