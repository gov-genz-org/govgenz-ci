<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Page programme Déclaration : bandeau (stats) + corps éditorial en blocs CMS.
 */
final class DeclarationProgramPage
{
    /**
     * Corps éditorial en blocs CMS (sans grilles « cartes déclaration » legacy).
     *
     * @param list<array<string, mixed>> $blocks
     *
     * @return array{
     *     stats: list<array{value: string, suffix: string, label: string}>,
     *     body: list<array<string, mixed>>
     * }
     */
    public static function partitionBlocks(array $blocks): array
    {
        $body = [];

        foreach ($blocks as $blk) {
            if (! is_array($blk)) {
                continue;
            }
            $type = strtolower(trim((string) ($blk['type'] ?? '')));

            if ($type === 'cards_grid') {
                $variant = strtolower(trim((string) ($blk['variant'] ?? '')));
                if ($variant === 'declaration_cards') {
                    continue;
                }
            }

            $body[] = $blk;
        }

        return ['stats' => [], 'body' => $body];
    }

    /**
     * @param array<string, mixed> $page
     *
     * @return array{
     *     stats: list<array{value: string, suffix: string, label: string}>,
     *     body: list<array<string, mixed>>
     * }
     */
    public static function bodyFromPage(array $page): array
    {
        $raw = $page['body_blocks'] ?? null;
        if ($raw === null || trim((string) $raw) === '' || trim((string) $raw) === '[]') {
            return ['stats' => [], 'body' => []];
        }

        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? self::partitionBlocks($decoded) : ['stats' => [], 'body' => []];
    }

    /**
     * Corps statique découpé avant / après le bloc partenariats (liste BDD).
     *
     * @param list<array<string, mixed>> $blocks
     *
     * @return array{before: list<array<string, mixed>>, after: list<array<string, mixed>>}
     */
    public static function splitStaticAroundPartnerships(array $blocks): array
    {
        $before = [];
        $after  = [];
        $past   = false;

        foreach ($blocks as $blk) {
            if (! is_array($blk)) {
                continue;
            }
            $type = strtolower(trim((string) ($blk['type'] ?? '')));
            if ($type === 'legal_prose' || $type === 'cta_panel') {
                $past = true;
            }
            if ($past) {
                $after[] = $blk;
            } else {
                if ($type === 'cards_grid') {
                    $variant = strtolower(trim((string) ($blk['variant'] ?? '')));
                    if ($variant === 'declaration_cards') {
                        continue;
                    }
                }
                $before[] = $blk;
            }
        }

        return ['before' => $before, 'after' => $after];
    }

    /**
     * Index du premier bloc qui bascule en zone « après partenariats » (null = tout avant).
     *
     * @param list<array<string, mixed>> $blocks
     */
    public static function splitIndexAt(array $blocks): ?int
    {
        foreach ($blocks as $idx => $blk) {
            if (! is_array($blk)) {
                continue;
            }
            $type = strtolower(trim((string) ($blk['type'] ?? '')));
            if ($type === 'legal_prose' || $type === 'cta_panel') {
                return $idx;
            }
        }

        return null;
    }

    /**
     * @param list<array<string, mixed>> $blocks
     *
     * @return list<'before'|'after'>
     */
    public static function blockZoneLabels(array $blocks): array
    {
        $splitAt = self::splitIndexAt($blocks);
        $labels  = [];

        foreach ($blocks as $idx => $blk) {
            if (! is_array($blk)) {
                continue;
            }
            $labels[$idx] = $splitAt !== null && $idx >= $splitAt ? 'after' : 'before';
        }

        return $labels;
    }

}
