<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Corrige le slug CMS secteur → secteurs/sectors (URLs publiques) et assure le bloc sectors_grid.
 */
class FixSecteursPageSlugAndSectorsGridBlock extends Migration
{
    /** @var array<int, array{0: string, 1: string, 2: string}> */
    private const SLUG_RENAMES = [
        ['secteur', 'fr', 'secteurs'],
        ['secteur', 'en', 'sectors'],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        foreach (self::SLUG_RENAMES as [$from, $locale, $to]) {
            $this->renameSlugIfFree($from, $locale, $to);
        }

        $now = date('Y-m-d H:i:s');
        foreach (['secteurs' => 'fr', 'sectors' => 'en'] as $slug => $locale) {
            $row = $this->db->table('cms_pages')->where('slug', $slug)->where('locale', $locale)->get()->getRowArray();
            if ($row === null) {
                continue;
            }

            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw === '' || $raw === '[]' || ! str_contains($raw, 'sectors_grid')) {
                $blocks = [['type' => 'sectors_grid', 'layout' => 'wide']];
                $this->db->table('cms_pages')->where('id', (int) ($row['id'] ?? 0))->update([
                    'content_mode' => 'blocks',
                    'body_html'    => '',
                    'body_blocks'  => json_encode($blocks, JSON_UNESCAPED_UNICODE),
                    'updated_at'   => $now,
                ]);
            }

            CmsPublishedPageCache::forget($locale, $slug);
            CmsPublishedPageCache::forget($locale, 'secteur');
        }
    }

    public function down(): void
    {
    }

    private function renameSlugIfFree(string $from, string $locale, string $to): void
    {
        $row = $this->db->table('cms_pages')
            ->where('slug', $from)
            ->where('locale', $locale)
            ->get()
            ->getFirstRow('array');

        if ($row === null) {
            return;
        }

        CmsPublishedPageCache::forget($locale, $from);

        $targetTaken = $this->db->table('cms_pages')
            ->where('slug', $to)
            ->where('locale', $locale)
            ->where('id !=', (int) ($row['id'] ?? 0))
            ->countAllResults() > 0;

        if ($targetTaken) {
            return;
        }

        $this->db->table('cms_pages')
            ->where('id', (int) $row['id'])
            ->update(['slug' => $to, 'updated_at' => date('Y-m-d H:i:s')]);

        CmsPublishedPageCache::forget($locale, $to);
    }
}
