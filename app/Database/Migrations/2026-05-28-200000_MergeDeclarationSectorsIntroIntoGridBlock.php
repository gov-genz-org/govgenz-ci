<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Fusionne section_text + html (decl-teams-header) + sectors_grid en un seul bloc sectors_grid.
 */
class MergeDeclarationSectorsIntroIntoGridBlock extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $rows = $this->db->table('cms_pages')
            ->where('translation_group', 'declaration-program-page')
            ->get()
            ->getResultArray();

        $now = date('Y-m-d H:i:s');
        foreach ($rows as $row) {
            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw === '' || $raw === '[]') {
                continue;
            }

            $blocks = json_decode($raw, true);
            if (! is_array($blocks)) {
                continue;
            }

            $merged = $this->mergeBlocks($blocks);
            if ($merged === null) {
                continue;
            }

            $locale = (string) ($row['locale'] ?? 'fr');
            $slug   = (string) ($row['slug'] ?? '');

            $this->db->table('cms_pages')->where('id', (int) ($row['id'] ?? 0))->update([
                'body_blocks' => json_encode($merged, JSON_UNESCAPED_UNICODE),
                'updated_at'  => $now,
            ]);

            CmsPublishedPageCache::forget($locale, $slug);
        }
    }

    public function down(): void
    {
    }

    /**
     * @param list<mixed> $blocks
     * @return ?list<array<string, mixed>>
     */
    private function mergeBlocks(array $blocks): ?array
    {
        $out    = [];
        $n      = count($blocks);
        $i      = 0;
        $changed = false;

        while ($i < $n) {
            if ($this->isMergeCandidate($blocks, $i)) {
                $out[] = $this->buildMergedBlock(
                    $blocks[$i],
                    $blocks[$i + 1],
                    $blocks[$i + 2]
                );
                $i += 3;
                $changed = true;

                continue;
            }

            if (! is_array($blocks[$i])) {
                $i++;

                continue;
            }

            $out[] = $blocks[$i];
            $i++;
        }

        return $changed ? $out : null;
    }

    /**
     * @param list<mixed> $blocks
     */
    private function isMergeCandidate(array $blocks, int $i): bool
    {
        if ($i + 2 >= count($blocks)) {
            return false;
        }

        $textBlk = $blocks[$i];
        $htmlBlk = $blocks[$i + 1];
        $gridBlk = $blocks[$i + 2];

        if (! is_array($textBlk) || ! is_array($htmlBlk) || ! is_array($gridBlk)) {
            return false;
        }

        if (strtolower(trim((string) ($textBlk['type'] ?? ''))) !== 'section_text') {
            return false;
        }

        if (strtolower(trim((string) ($htmlBlk['type'] ?? ''))) !== 'html') {
            return false;
        }

        if (strtolower(trim((string) ($gridBlk['type'] ?? ''))) !== 'sectors_grid') {
            return false;
        }

        $html = (string) ($htmlBlk['html'] ?? '');

        return str_contains($html, 'decl-teams-header');
    }

    /**
     * @param array<string, mixed> $textBlk
     * @param array<string, mixed> $htmlBlk
     * @param array<string, mixed> $gridBlk
     * @return array<string, mixed>
     */
    private function buildMergedBlock(array $textBlk, array $htmlBlk, array $gridBlk): array
    {
        $paragraphs = $textBlk['paragraphs'] ?? [];
        if (! is_array($paragraphs)) {
            $paragraphs = [];
        }

        $gridBlk['kicker'] = trim((string) ($paragraphs[0] ?? ''));
        $gridBlk['title']  = trim((string) ($paragraphs[1] ?? ''));
        $gridBlk['lead']   = trim((string) ($paragraphs[2] ?? ''));

        $html = (string) ($htmlBlk['html'] ?? '');
        if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $html, $titleMatch) === 1) {
            $gridBlk['banner_title'] = trim(html_entity_decode(strip_tags((string) ($titleMatch[1] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('/<span[^>]*>(.*?)<\/span>/is', $html, $subMatch) === 1) {
            $gridBlk['banner_subtitle'] = trim(html_entity_decode(strip_tags((string) ($subMatch[1] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return $gridBlk;
    }
}
