<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsGovgenzHtmlBodies;
use App\Database\Support\CmsGovgenzHtmlBodiesEn;
use CodeIgniter\Database\Migration;

/**
 * Pages CMS : organization_hub → structures_grid ; page structure → embed BDD.
 */
class MigrateAllCmsStructureContentToBdd extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('cms_pages')) {
            $this->migrateBodyBlocks();
            $this->migrateStructurePageHtml();
        }
    }

    public function down(): void
    {
    }

    private function migrateBodyBlocks(): void
    {
        $rows = $this->db->table('cms_pages')
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
                $type = strtolower(trim((string) ($blk['type'] ?? '')));

                if ($type === 'organization_hub') {
                    $decoded[$idx] = ['type' => 'structures_grid', 'layout' => 'hub'];
                    $changed = true;

                    continue;
                }

                if ($type === 'cards_grid'
                    && strtolower(trim((string) ($blk['variant'] ?? ''))) === 'dept_cards') {
                    $decoded[$idx] = ['type' => 'structures_grid', 'layout' => 'dept'];
                    $changed = true;
                }
            }

            if (! $changed) {
                continue;
            }

            $this->db->table('cms_pages')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'body_blocks'  => json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE),
                    'content_mode' => 'blocks',
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
        }
    }

    private function migrateStructurePageHtml(): void
    {
        foreach (['fr' => CmsGovgenzHtmlBodies::class, 'en' => CmsGovgenzHtmlBodiesEn::class] as $locale => $class) {
            $body = $class::structure();
            $rows = $this->db->table('cms_pages')
                ->where('slug', 'structure')
                ->where('locale', $locale)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                if (strtolower(trim((string) ($row['content_mode'] ?? ''))) === 'blocks') {
                    continue;
                }

                $this->db->table('cms_pages')
                    ->where('id', (int) ($row['id'] ?? 0))
                    ->update([
                        'body_html'  => $body,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }
    }
}
