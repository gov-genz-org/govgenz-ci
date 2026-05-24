<?php

declare(strict_types=1);

if (! function_exists('project_parse_budget_display_to_ariary')) {
    /**
     * Interprète budget_display (texte libre) en ariary pour agrégation.
     * Formats gérés : « X Mds Ar », « X M Ar », nombre + « Ar » (ariary bruts).
     *
     * @return float|null null si aucune valeur numérique exploitable
     */
    function project_parse_budget_display_to_ariary(?string $display): ?float
    {
        $s = trim((string) $display);
        if ($s === '') {
            return null;
        }

        if (preg_match('/([\d\s\x{00A0}\x{202F}]+(?:[.,]\d+)?)\s*mds?\b/iu', $s, $m)) {
            return project_normalize_budget_number_token($m[1]) * 1_000_000_000.0;
        }

        if (preg_match('/([\d\s\x{00A0}\x{202F}]+(?:[.,]\d+)?)\s+m\s*ar\b/iu', $s, $m)) {
            return project_normalize_budget_number_token($m[1]) * 1_000_000.0;
        }

        if (preg_match('/([\d\s\x{00A0}\x{202F}]+(?:[.,]\d+)?)\s+m\b(?![a-zàâäéèêëïîôùûüç])/iu', $s, $m)) {
            return project_normalize_budget_number_token($m[1]) * 1_000_000.0;
        }

        if (preg_match('/([\d\s\x{00A0}\x{202F}]+(?:[.,]\d+)?)\s*ar\b/iu', $s, $m)) {
            return project_normalize_budget_number_token($m[1]);
        }

        return null;
    }
}

if (! function_exists('project_parse_montant_cell_to_ariary')) {
    /**
     * Montant d’une cellule du tableau budget (Ar bruts, « 400 000 000 », « 55 M Ar », etc.).
     */
    function project_parse_montant_cell_to_ariary(?string $montant): ?float
    {
        $s = trim((string) $montant);
        if ($s === '') {
            return null;
        }

        $fromDisplay = project_parse_budget_display_to_ariary($s);
        if ($fromDisplay !== null && $fromDisplay > 0) {
            return $fromDisplay;
        }

        if (preg_match('/^[\d\s\x{00A0}\x{202F}.,]+$/u', $s) === 1) {
            $n = project_normalize_budget_number_token($s);

            return $n > 0 ? $n : null;
        }

        return null;
    }
}

if (! function_exists('project_budget_admin_parts_from_ariary')) {
    /**
     * @return array{
     *   budget_amount: float,
     *   budget_scale: string,
     *   budget_ariary: int,
     *   budget_display: string
     * }
     */
    function project_budget_admin_parts_from_ariary(float $ariary, string $locale): array
    {
        $ariaryInt = (int) round($ariary);

        if ($ariary >= 1_000_000_000.0) {
            $amount = $ariary / 1_000_000_000.0;
            $scale  = \App\Models\ProjectProjectModel::BUDGET_SCALE_BILLION;
        } elseif ($ariary >= 1_000_000.0) {
            $amount = $ariary / 1_000_000.0;
            $scale  = \App\Models\ProjectProjectModel::BUDGET_SCALE_MILLION;
        } elseif ($ariary >= 1_000.0) {
            $amount = $ariary / 1_000.0;
            $scale  = \App\Models\ProjectProjectModel::BUDGET_SCALE_THOUSAND;
        } else {
            $amount = $ariary;
            $scale  = \App\Models\ProjectProjectModel::BUDGET_SCALE_ARIARY;
        }

        $amount = round($amount, 4);
        if (fmod($amount, 1.0) === 0.0) {
            $amount = (float) (int) $amount;
        }

        return [
            'budget_amount'  => $amount,
            'budget_scale'   => $scale,
            'budget_ariary'  => $ariaryInt,
            'budget_display' => project_format_budget_display_from_parts($amount, $scale, $locale),
        ];
    }
}

if (! function_exists('project_normalize_budget_number_token')) {
    /**
     * Extrait un flottant depuis un fragment type « 850 », « 2,5 », « 1 234,5 » (espaces = milliers).
     */
    function project_normalize_budget_number_token(string $raw): float
    {
        $t = preg_replace('/[\s\x{00A0}\x{202F}]+/u', '', trim($raw)) ?? '';
        if ($t === '') {
            return 0.0;
        }
        $t = str_replace(',', '.', $t);

        return (float) $t;
    }
}

if (! function_exists('project_format_ariary_table_cell')) {
    function project_format_ariary_table_cell(float $ariary, string $locale): string
    {
        if ($locale === 'en') {
            return number_format($ariary, 0, '.', ',');
        }

        return number_format($ariary, 0, ',', ' ');
    }
}

if (! function_exists('project_budget_table_sum_ariary')) {
    /**
     * Somme des lignes hors ligne « total ».
     *
     * @param list<array<string, mixed>> $rows
     */
    function project_budget_table_sum_ariary(array $rows): ?float
    {
        helper('admin');
        $sum = 0.0;
        $n   = 0;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (\App\Libraries\ProjectBudgetTableSync::rowIsTotal($row)) {
                continue;
            }
            $poste   = admin_pp_scrub_junk_text(trim((string) ($row['poste'] ?? '')));
            $detail  = admin_pp_scrub_junk_text(trim((string) ($row['detail'] ?? '')));
            $montant = admin_pp_scrub_junk_text(trim((string) ($row['montant'] ?? '')));
            if ($poste === '' && $detail === '' && $montant === '') {
                continue;
            }
            $ariary = project_parse_montant_cell_to_ariary($montant);
            if ($ariary === null || $ariary <= 0) {
                continue;
            }
            $sum += $ariary;
            $n++;
        }

        return $n > 0 ? $sum : null;
    }
}

