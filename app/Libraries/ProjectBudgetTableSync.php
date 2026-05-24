<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Somme des lignes du bloc budget_table → total du tableau + budget carte (montant / aperçu).
 */
final class ProjectBudgetTableSync
{
    /**
     * Retire les lignes « total » stockées (total calculé à l’affichage uniquement).
     *
     * @param list<array<string, mixed>> $blocks
     * @return list<array<string, mixed>>
     */
    public static function stripStoredTotalMontants(array $blocks): array
    {
        $out = [];
        foreach ($blocks as $block) {
            if (! is_array($block) || (string) ($block['type'] ?? '') !== 'budget_table') {
                $out[] = $block;
                continue;
            }
            $rows = $block['rows'] ?? [];
            if (! is_array($rows)) {
                $out[] = $block;
                continue;
            }
            $newRows = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (self::rowIsTotal($row)) {
                    continue;
                }
                $newRows[] = $row;
            }
            $block['rows'] = $newRows;
            $out[]         = $block;
        }

        return $out;
    }

    /**
     * Budget carte (montant, unité, aperçu) dérivé de la somme des blocs budget_table.
     *
     * @param list<array<string, mixed>> $blocks
     * @return array{
     *   budget_amount: float,
     *   budget_scale: string,
     *   budget_ariary: int,
     *   budget_display: string
     * }|null
     */
    public static function budgetPayloadFromBlocks(array $blocks, string $locale): ?array
    {
        helper('project');
        $sum = self::sumAllBudgetTables($blocks);
        if ($sum === null || $sum <= 0) {
            return null;
        }

        return project_budget_admin_parts_from_ariary($sum, $locale);
    }

    /**
     * @param list<array<string, mixed>> $blocks
     */
    public static function sumAllBudgetTables(array $blocks): ?float
    {
        helper('project');
        $total = 0.0;
        $found = false;
        foreach ($blocks as $block) {
            if (! is_array($block) || (string) ($block['type'] ?? '') !== 'budget_table') {
                continue;
            }
            $rows = $block['rows'] ?? [];
            if (! is_array($rows)) {
                continue;
            }
            $part = project_budget_table_sum_ariary($rows);
            if ($part === null || $part <= 0) {
                continue;
            }
            $total += $part;
            $found = true;
        }

        return $found ? $total : null;
    }

    /**
     * @param array<string, mixed> $bodyPayload
     * @param array{
     *   budget_amount: float|null,
     *   budget_scale: string|null,
     *   budget_ariary: int|null,
     *   budget_display: string|null
     * } $budgetPayload
     * @return array{
     *   body: array<string, mixed>,
     *   budget: array{
     *     budget_amount: float|null,
     *     budget_scale: string|null,
     *     budget_ariary: int|null,
     *     budget_display: string|null
     *   }
     * }
     */
    public static function applyToPayloads(array $bodyPayload, array $budgetPayload, string $locale): array
    {
        if (($bodyPayload['body_content_mode'] ?? '') !== 'blocks') {
            return ['body' => $bodyPayload, 'budget' => $budgetPayload];
        }

        $raw = $bodyPayload['body_blocks'] ?? '';
        if ($raw === null || $raw === '') {
            return ['body' => $bodyPayload, 'budget' => $budgetPayload];
        }

        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded) || $decoded === []) {
            return ['body' => $bodyPayload, 'budget' => $budgetPayload];
        }

        $stripped = self::stripStoredTotalMontants($decoded);
        $bodyPayload['body_blocks'] = json_encode($stripped, JSON_UNESCAPED_UNICODE);

        $fromTable = self::budgetPayloadFromBlocks($stripped, $locale);
        if ($fromTable !== null) {
            $budgetPayload = $fromTable;
        }

        return ['body' => $bodyPayload, 'budget' => $budgetPayload];
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function rowIsTotal(array $row): bool
    {
        if (strtolower(trim((string) ($row['row_class'] ?? ''))) === 'total') {
            return true;
        }

        if (strtolower(trim((string) ($row['is_total'] ?? ''))) === '1'
            || strtolower(trim((string) ($row['is_total'] ?? ''))) === 'yes') {
            return true;
        }

        $poste = trim((string) ($row['poste'] ?? ''));

        return preg_match('/\btotal\b/iu', $poste) === 1;
    }
}
