<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Fusionne section_text (+ html bandeau optionnel) + structures_grid en un seul bloc.
 */
class MergeDeclarationStructuresIntroIntoGridBlock extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $rows = $this->db->table('cms_pages')
            ->get()
            ->getResultArray();

        $now = date('Y-m-d H:i:s');
        foreach ($rows as $row) {
            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw === '' || $raw === '[]' || ! str_contains($raw, 'structures_grid')) {
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
        $out     = [];
        $n       = count($blocks);
        $i       = 0;
        $changed = false;

        while ($i < $n) {
            if ($this->isThreeBlockMerge($blocks, $i)) {
                $out[] = $this->buildMergedBlock($blocks[$i], $blocks[$i + 1], $blocks[$i + 2]);
                $i += 3;
                $changed = true;

                continue;
            }

            if ($this->isTwoBlockMerge($blocks, $i)) {
                $out[] = $this->buildMergedBlock($blocks[$i], null, $blocks[$i + 1]);
                $i += 2;
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
    private function isTwoBlockMerge(array $blocks, int $i): bool
    {
        if ($i + 1 >= count($blocks)) {
            return false;
        }

        return $this->isSectionTextBlock($blocks[$i])
            && $this->isStructuresGridBlock($blocks[$i + 1])
            && ! $this->blockHasIntro($blocks[$i + 1]);
    }

    /**
     * @param list<mixed> $blocks
     */
    private function isThreeBlockMerge(array $blocks, int $i): bool
    {
        if ($i + 2 >= count($blocks)) {
            return false;
        }

        if (! $this->isSectionTextBlock($blocks[$i]) || ! $this->isHtmlBannerBlock($blocks[$i + 1])) {
            return false;
        }

        return $this->isStructuresGridBlock($blocks[$i + 2])
            && ! $this->blockHasIntro($blocks[$i + 2]);
    }

    /**
     * @param array<string, mixed> $gridBlk
     */
    private function blockHasIntro(array $gridBlk): bool
    {
        return trim((string) ($gridBlk['kicker'] ?? '') . (string) ($gridBlk['title'] ?? '') . (string) ($gridBlk['lead'] ?? '')) !== '';
    }

    private function isSectionTextBlock(mixed $blk): bool
    {
        return is_array($blk) && strtolower(trim((string) ($blk['type'] ?? ''))) === 'section_text';
    }

    private function isStructuresGridBlock(mixed $blk): bool
    {
        return is_array($blk) && strtolower(trim((string) ($blk['type'] ?? ''))) === 'structures_grid';
    }

    private function isHtmlBannerBlock(mixed $blk): bool
    {
        if (! is_array($blk) || strtolower(trim((string) ($blk['type'] ?? ''))) !== 'html') {
            return false;
        }

        $html = (string) ($blk['html'] ?? '');

        return str_contains($html, 'decl-teams-header');
    }

    /**
     * @param array<string, mixed> $textBlk
     * @param ?array<string, mixed> $htmlBlk
     * @param array<string, mixed> $gridBlk
     * @return array<string, mixed>
     */
    private function buildMergedBlock(array $textBlk, ?array $htmlBlk, array $gridBlk): array
    {
        $paragraphs = $textBlk['paragraphs'] ?? [];
        if (! is_array($paragraphs)) {
            $paragraphs = [];
        }

        $gridBlk['kicker'] = trim((string) ($paragraphs[0] ?? ''));
        $gridBlk['title']  = trim((string) ($paragraphs[1] ?? ''));
        $gridBlk['lead']   = trim((string) ($paragraphs[2] ?? ''));

        if ($htmlBlk !== null) {
            $html = (string) ($htmlBlk['html'] ?? '');
            if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $html, $titleMatch) === 1) {
                $gridBlk['banner_title'] = trim(html_entity_decode(strip_tags((string) ($titleMatch[1] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (preg_match('/<span[^>]*>(.*?)<\/span>/is', $html, $subMatch) === 1) {
                $gridBlk['banner_subtitle'] = trim(html_entity_decode(strip_tags((string) ($subMatch[1] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        return $gridBlk;
    }
}
