<?php

declare(strict_types=1);

if (! function_exists('project_budget_infer_parts_from_legacy')) {
    /**
     * Déduit montant + échelle depuis budget_display historique (migration / import).
     *
     * @return array{amount: float, scale: string, ariary: int}|null
     */
    function project_budget_infer_parts_from_legacy(?string $display): ?array
    {
        $s = trim((string) $display);
        if ($s === '') {
            return null;
        }
        $ariary = project_parse_budget_display_to_ariary($s);
        if ($ariary === null || $ariary <= 0) {
            return null;
        }
        $ariaryInt = (int) round($ariary);

        if (preg_match('/mds?\b|milliard|billion|\bbn\b/iu', $s) === 1) {
            return [
                'amount' => $ariary / 1_000_000_000.0,
                'scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_BILLION,
                'ariary' => $ariaryInt,
            ];
        }
        if (preg_match('/\bm\s+ar\b|\d\s+m\b/iu', $s) === 1) {
            return [
                'amount' => $ariary / 1_000_000.0,
                'scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_MILLION,
                'ariary' => $ariaryInt,
            ];
        }
        if (preg_match('/\bk\s+ar\b|\d\s+k\b/iu', $s) === 1) {
            return [
                'amount' => $ariary / 1_000.0,
                'scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_THOUSAND,
                'ariary' => $ariaryInt,
            ];
        }
        if ($ariaryInt >= 1_000_000_000 && $ariaryInt % 1_000_000_000 === 0) {
            return [
                'amount' => $ariaryInt / 1_000_000_000.0,
                'scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_BILLION,
                'ariary' => $ariaryInt,
            ];
        }
        if ($ariaryInt >= 1_000_000 && $ariaryInt % 1_000_000 === 0) {
            return [
                'amount' => $ariaryInt / 1_000_000.0,
                'scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_MILLION,
                'ariary' => $ariaryInt,
            ];
        }
        if ($ariaryInt >= 1_000 && $ariaryInt % 1_000 === 0) {
            return [
                'amount' => $ariaryInt / 1_000.0,
                'scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_THOUSAND,
                'ariary' => $ariaryInt,
            ];
        }

        return [
            'amount' => (float) $ariaryInt,
            'scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_ARIARY,
            'ariary' => $ariaryInt,
        ];
    }
}

if (! function_exists('project_budget_scale_multiplier')) {
    function project_budget_scale_multiplier(string $scale): ?float
    {
        return match ($scale) {
            \App\Models\ProjectProjectModel::BUDGET_SCALE_ARIARY   => 1.0,
            \App\Models\ProjectProjectModel::BUDGET_SCALE_THOUSAND => 1_000.0,
            \App\Models\ProjectProjectModel::BUDGET_SCALE_MILLION  => 1_000_000.0,
            \App\Models\ProjectProjectModel::BUDGET_SCALE_BILLION  => 1_000_000_000.0,
            default => null,
        };
    }
}

if (! function_exists('project_budget_ariary_from_parts')) {
    function project_budget_ariary_from_parts(float $amount, string $scale): ?int
    {
        if ($amount <= 0) {
            return null;
        }
        $mult = project_budget_scale_multiplier($scale);
        if ($mult === null) {
            return null;
        }
        $ariary = $amount * $mult;

        return $ariary > 0 ? (int) round($ariary) : null;
    }
}

if (! function_exists('project_exchange_rates_config')) {
    /**
     * Taux indicatifs (BDD admin, sinon valeurs par défaut 2026).
     *
     * @return array{
     *   label_year: string,
     *   usd_ariary: float,
     *   eur_ariary: float,
     *   cny_ariary: float,
     *   jpy_ariary: float,
     *   fcfa_ariary: float
     * }
     */
    function project_exchange_rates_config(): array
    {
        try {
            return model(\App\Models\ProjectExchangeRateModel::class)->getConfig();
        } catch (\Throwable) {
            return \App\Models\ProjectExchangeRateModel::defaults();
        }
    }
}

if (! function_exists('project_format_budget_display_from_parts')) {
    function project_format_budget_display_from_parts(float $amount, string $scale, string $locale): string
    {
        helper('language');
        $dec = fmod($amount, 1.0) === 0.0 ? 0 : 2;
        if ($locale === 'en') {
            $num = number_format($amount, $dec, '.', ',');
        } else {
            $num = number_format($amount, $dec, ',', ' ');
        }

        return match ($scale) {
            \App\Models\ProjectProjectModel::BUDGET_SCALE_BILLION => $num . ' ' . (
                $locale === 'en'
                    ? lang('Projects.stats_budget_suffix_billion_en')
                    : lang('Projects.stats_budget_suffix_mds_fr')
            ),
            \App\Models\ProjectProjectModel::BUDGET_SCALE_MILLION => $num . ' ' . (
                $locale === 'en'
                    ? lang('Projects.stats_budget_suffix_million_en')
                    : lang('Projects.stats_budget_suffix_m_fr')
            ),
            \App\Models\ProjectProjectModel::BUDGET_SCALE_THOUSAND => $num . ' ' . (
                $locale === 'en' ? 'k Ar' : 'k Ar'
            ),
            default => $num . ' ' . (
                $locale === 'en'
                    ? lang('Projects.stats_budget_suffix_ar_en')
                    : lang('Projects.stats_budget_suffix_ar_fr')
            ),
        };
    }
}

if (! function_exists('project_currency_equivalents_for_project')) {
    /**
     * @return array<string, string>|null
     */
    function project_currency_equivalents_for_project(array $project, string $locale): ?array
    {
        $ariary = project_budget_ariary_for_project($project);
        if ($ariary === null || $ariary <= 0) {
            return null;
        }

        return project_currency_equivalents_from_ariary($ariary, $locale);
    }
}

if (! function_exists('project_format_exchange_rate_display')) {
    function project_format_exchange_rate_display(float $rate, string $locale): string
    {
        $dec = fmod($rate, 1.0) === 0.0 ? 0 : 1;
        if ($locale === 'en') {
            return number_format($rate, $dec, '.', ',');
        }

        return number_format($rate, $dec, ',', ' ');
    }
}

if (! function_exists('project_format_budget_ariary_sum')) {
    /**
     * Affiche une somme en ariary dans le même esprit que le statique (M / Mds Ar).
     */
    function project_format_budget_ariary_sum(float $ariary, string $locale): string
    {
        helper('language');
        if ($ariary <= 0) {
            return lang('Projects.stats_value_emdash');
        }

        $b = 1_000_000_000.0;
        $m = 1_000_000.0;

        if ($ariary >= $b) {
            $x   = $ariary / $b;
            $dec = abs($x - round($x)) < 1e-6 ? 0 : 1;
            if ($locale === 'en') {
                $num = number_format($x, $dec, '.', ',');

                return $num . ' ' . lang('Projects.stats_budget_suffix_billion_en');
            }
            $num = number_format($x, $dec, ',', ' ');

            return $num . ' ' . lang('Projects.stats_budget_suffix_mds_fr');
        }

        if ($ariary >= $m) {
            $x   = $ariary / $m;
            $dec = abs($x - round($x)) < 1e-6 ? 0 : 1;
            if ($locale === 'en') {
                $num = number_format($x, $dec, '.', ',');

                return $num . ' ' . lang('Projects.stats_budget_suffix_million_en');
            }
            $num = number_format($x, $dec, ',', ' ');

            return $num . ' ' . lang('Projects.stats_budget_suffix_m_fr');
        }

        if ($locale === 'en') {
            return number_format($ariary, 0, '.', ',') . ' ' . lang('Projects.stats_budget_suffix_ar_en');
        }

        return number_format($ariary, 0, ',', ' ') . ' ' . lang('Projects.stats_budget_suffix_ar_fr');
    }
}

if (! function_exists('project_currency_rates_header_html')) {
    /** En-tête widget équivalences (HTML sûr, chiffres échappés). */
    function project_currency_rates_header_html(string $locale): string
    {
        $r = project_exchange_rates_config();
        $label = esc($r['label_year'], 'html');
        $usd = esc(project_format_exchange_rate_display($r['usd_ariary'], $locale), 'html');
        $eur = esc(project_format_exchange_rate_display($r['eur_ariary'], $locale), 'html');
        $cny = esc(project_format_exchange_rate_display($r['cny_ariary'], $locale), 'html');
        $jpy = esc(project_format_exchange_rate_display($r['jpy_ariary'], $locale), 'html');
        $fcfa = esc(project_format_exchange_rate_display($r['fcfa_ariary'], $locale), 'html');

        if ($locale === 'en') {
            return 'Approx. rates ' . $label . ':<br>'
                . '<strong>1 USD = ' . $usd . ' Ar · 1 EUR = ' . $eur . ' Ar</strong><br>'
                . '<strong>1 CNY = ' . $cny . ' Ar · 1 JPY = ' . $jpy . ' Ar · 1 XOF = ' . $fcfa . ' Ar</strong>';
        }

        return 'Taux approx. ' . $label . ' :<br>'
            . '<strong>1 USD = ' . $usd . ' Ar · 1 EUR = ' . $eur . ' Ar</strong><br>'
            . '<strong>1 CNY = ' . $cny . ' Ar · 1 JPY = ' . $jpy . ' Ar · 1 FCFA = ' . $fcfa . ' Ar</strong>';
    }
}

if (! function_exists('project_currency_equivalents_from_ariary')) {
    /**
     * @return array<string, string>|null
     */
    function project_currency_equivalents_from_ariary(float $ariary, string $locale): ?array
    {
        if ($ariary <= 0) {
            return null;
        }

        $rates = project_exchange_rates_config();

        $fmt = static function (float $n, int $dec = 0) use ($locale): string {
            if ($locale === 'en') {
                return number_format($n, $dec, '.', ',');
            }

            return number_format($n, $dec, ',', ' ');
        };

        $usd = $ariary / $rates['usd_ariary'];
        $eur = $ariary / $rates['eur_ariary'];
        $cny = $ariary / $rates['cny_ariary'];
        $xof = $ariary / $rates['fcfa_ariary'];
        $jpy = $ariary / $rates['jpy_ariary'];

        if ($locale === 'en') {
            return [
                '🇺🇸 USD' => '~' . $fmt($usd) . ' $',
                '🇪🇺 EUR' => '~' . $fmt($eur) . ' €',
                '🇨🇳 CNY' => '~' . $fmt($cny) . ' CNY',
                '🌍 XOF' => '~' . $fmt($xof / 1_000_000, 1) . ' M XOF',
                '🇯🇵 JPY' => '~' . $fmt($jpy / 1_000_000, 1) . ' M ¥',
            ];
        }

        return [
            '🇺🇸 USD' => '~' . $fmt($usd) . ' $',
            '🇪🇺 EUR' => '~' . $fmt($eur) . ' €',
            '🇨🇳 CNY (Yuan)' => '~' . $fmt($cny) . ' 元',
            '🌍 FCFA' => '~' . $fmt($xof / 1_000_000, 1) . ' M FCFA',
            '🇯🇵 JPY (Yen)' => '~' . $fmt($jpy / 1_000_000, 1) . ' M ¥',
        ];
    }
}

if (! function_exists('project_budget_ariary_for_project')) {
    /**
     * Montant canonique en ariary (champ structuré, sinon repli sur l’ancien texte).
     */
    function project_budget_ariary_for_project(array $project): ?float
    {
        if (isset($project['budget_ariary']) && $project['budget_ariary'] !== null && $project['budget_ariary'] !== '') {
            $n = (float) $project['budget_ariary'];

            return $n > 0 ? $n : null;
        }

        return project_parse_budget_display_to_ariary($project['budget_display'] ?? null);
    }
}

