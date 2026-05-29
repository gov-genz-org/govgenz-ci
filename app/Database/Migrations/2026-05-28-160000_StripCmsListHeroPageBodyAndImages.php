<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Bandeaux de listes : retirer corps, blocs et image hero en base (non utilisés sur le site).
 */
class StripCmsListHeroPageBodyAndImages extends Migration
{
    /** @var list<string> */
    private const SLUGS = ['press', 'projects', 'positions'];

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('cms_pages')
            ->whereIn('slug', self::SLUGS)
            ->update([
                'body_html'       => '',
                'body_blocks'     => null,
                'content_mode'    => 'html',
                'layout_key'      => 'full',
                'hero_image_id'   => null,
                'hero_image_alt'  => null,
                'updated_at'      => $now,
            ]);
    }

    public function down(): void
    {
        // Données éditoriales non restaurées.
    }
}
