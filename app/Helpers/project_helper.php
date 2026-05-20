<?php

declare(strict_types=1);

helper(['cms']);

if (! function_exists('project_asset_version')) {
    /**
     * @deprecated Préférer public_asset_url() ; conserve la version de déploiement globale.
     */
    function project_asset_version(string $relativePublicPath): string
    {
        helper('asset');

        return front_asset_version();
    }
}

if (! function_exists('project_public_asset_url')) {
    function project_public_asset_url(string $relativePublicPath): string
    {
        helper('asset');

        return public_asset_url($relativePublicPath);
    }
}

if (! function_exists('project_body_content_mode')) {
    /**
     * @param array<string, mixed> $project
     */
    function project_body_content_mode(array $project): string
    {
        $m = strtolower(trim((string) ($project['body_content_mode'] ?? 'html')));

        return $m === 'blocks' ? 'blocks' : 'html';
    }
}

if (! function_exists('project_render_main_body')) {
    /**
     * Colonne principale fiche projet : HTML brut ou blocs structurés (même gabarit CSS que le statique).
     *
     * @param array<string, mixed> $project
     */
    function project_render_main_body(array $project): string
    {
        if (project_body_content_mode($project) === 'blocks') {
            $raw = $project['body_blocks'] ?? null;
            if ($raw === null || $raw === '') {
                return '';
            }
            $decoded = json_decode((string) $raw, true);
            if (! is_array($decoded) || $decoded === []) {
                return '';
            }
            $locale = strtolower(trim((string) ($project['locale'] ?? 'fr')));
            if (! in_array($locale, ['fr', 'en'], true)) {
                $locale = 'fr';
            }
            $html = \App\Libraries\ProjectBodyBlocksRenderer::render($decoded, $locale);

            return cms_apply_html_embeds($html);
        }

        $body = (string) ($project['body'] ?? '');

        return cms_apply_html_embeds($body);
    }
}

if (! function_exists('project_render_fund_form_html')) {
    /**
     * Formulaire « Financer ce projet » (chaîne HTML vide si non applicable).
     *
     * @param array<string, mixed> $project
     */
    function project_render_fund_form_html(array $project): string
    {
        helper(['language', 'locale']);
        $showFundBudget   = project_has_financial_funding($project);
        $showFundMaterial = project_has_material_needs($project);
        if (! $showFundBudget && ! $showFundMaterial) {
            return '';
        }
        $slug = trim((string) ($project['slug'] ?? ''));
        if ($slug === '') {
            return '';
        }

        return view('front/projects/partials/fund_form', [
            'project'          => $project,
            'fundPostUrl'      => project_fund_post_url($slug),
            'showFundBudget'   => $showFundBudget,
            'showFundMaterial' => $showFundMaterial,
        ]);
    }
}

if (! function_exists('project_published_contributions_for_project')) {
    /**
     * Propositions validées (statut reviewed) affichables sur la fiche publique.
     *
     * @param array<string, mixed> $project
     *
     * @return list<array<string, mixed>>
     */
    function project_published_contributions_for_project(array $project): array
    {
        $slug = trim((string) ($project['slug'] ?? ''));
        if ($slug === '') {
            return [];
        }
        $rows = model(\App\Models\ProjectContributionModel::class)
            ->where('project_slug', $slug)
            ->where('status', \App\Models\ProjectContributionModel::STATUS_REVIEWED)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return is_array($rows) ? $rows : [];
    }
}

if (! function_exists('project_render_published_contributions_html')) {
    /**
     * Bloc HTML des soutiens validés (vide si aucun).
     *
     * @param array<string, mixed> $project
     */
    function project_render_published_contributions_html(array $project): string
    {
        helper('language');
        $contributions = project_published_contributions_for_project($project);
        if ($contributions === []) {
            return '';
        }

        return view('front/projects/partials/published_contributions', [
            'contributions' => $contributions,
        ]);
    }
}

if (! function_exists('project_append_html_inside_main')) {
    /**
     * Insère du HTML avant la fermeture de .project-main.
     */
    function project_append_html_inside_main(string $html, string $appendHtml): string
    {
        if ($appendHtml === '') {
            return $html;
        }
        if (preg_match(
            '#^(\s*<div\s+class=["\'][^"\']*project-main[^"\']*["\'][^>]*>)(.*)(</div>\s*(?:<!--\s*/project-main\s*-->)?\s*)$#is',
            $html,
            $m
        ) === 1) {
            return $m[1] . $m[2] . $appendHtml . $m[3];
        }
        if (str_contains($html, '<!-- /project-main -->')) {
            return str_replace('<!-- /project-main -->', $appendHtml . '<!-- /project-main -->', $html);
        }

        return $html . $appendHtml;
    }
}

if (! function_exists('project_render_main_column')) {
    /**
     * Colonne .project-main : évite un double wrapper si le seed HTML inclut déjà project-main.
     *
     * @param array<string, mixed> $project
     */
    function project_render_main_column(array $project): string
    {
        $html      = project_render_main_body($project);
        $published = project_render_published_contributions_html($project);

        if ($html === '' && $published === '') {
            return '';
        }
        if ($html === '') {
            return '<div class="project-main">' . $published . '</div>';
        }
        if ($published !== '') {
            if (preg_match('/class=["\'][^"\']*project-main[^"\']*["\']/i', $html) === 1) {
                $html = project_append_html_inside_main($html, $published);
            } else {
                $html = '<div class="project-main">' . $html . $published . '</div>';
            }

            return $html;
        }
        if (preg_match('/class=["\'][^"\']*project-main[^"\']*["\']/i', $html) === 1) {
            return $html;
        }

        return '<div class="project-main">' . $html . '</div>';
    }
}

if (! function_exists('project_status_badge_class')) {
    function project_status_badge_class(string $status): string
    {
        return match ($status) {
            \App\Models\ProjectProjectModel::STATUS_ACTIF       => 'status-actif',
            \App\Models\ProjectProjectModel::STATUS_CANDIDAT    => 'status-candidat',
            \App\Models\ProjectProjectModel::STATUS_VALIDATION => 'status-validation',
            \App\Models\ProjectProjectModel::STATUS_COMPLETE    => 'status-complete',
            default                                             => '',
        };
    }
}

if (! function_exists('project_format_launched_display')) {
    /**
     * Affichage type « Mars 2026 » (FR) ou « March 2026 » (EN).
     */
    function project_format_launched_display(?string $launchedAt, string $locale): string
    {
        $launchedAt = trim((string) $launchedAt);
        if ($launchedAt === '') {
            return '';
        }
        $ts = strtotime($launchedAt);
        if ($ts === false) {
            return $launchedAt;
        }
        if ($locale === 'en') {
            return date('F Y', $ts);
        }

        $months = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];
        $m = (int) date('n', $ts);
        $y = date('Y', $ts);

        return ucfirst($months[$m] ?? date('m', $ts)) . ' ' . $y;
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

/** @deprecated Utiliser project_currency_equivalents_for_project() */
if (! function_exists('project_currency_equivalents')) {
    /**
     * @return array<string, string>|null
     */
    function project_currency_equivalents(?string $budgetDisplay, string $locale): ?array
    {
        $ariary = project_parse_budget_display_to_ariary($budgetDisplay);
        if ($ariary === null || $ariary <= 0) {
            return null;
        }

        return project_currency_equivalents_from_ariary($ariary, $locale);
    }
}

if (! function_exists('project_sector_codes_from_csv')) {
    /**
     * @return list<string> codes minuscules uniques
     */
    function project_sector_codes_from_csv(string $csv): array
    {
        $seen = [];
        foreach (array_map('trim', explode(',', $csv)) as $code) {
            $c = strtolower($code);
            if ($c !== '' && ! isset($seen[$c])) {
                $seen[$c] = true;
            }
        }

        return array_keys($seen);
    }
}

if (! function_exists('project_public_url')) {
    /**
     * URL publique d’une fiche projet (préfixe /projects ou racine selon la config).
     * Respecte la locale (ex. /en/projects/…).
     */
    function project_public_url(string $slug): string
    {
        helper('locale');
        $slug = strtolower(trim($slug, '/'));
        if ($slug === '') {
            return \App\Libraries\SiteContext::projectsPathPrefixEnabled()
                ? localized_site_url('projects')
                : localized_site_url('');
        }
        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return localized_site_url('projects/' . $slug);
        }

        return localized_site_url($slug);
    }
}

if (! function_exists('project_share_qr_image_path')) {
    function project_share_qr_image_path(string $slug): string
    {
        $slug = strtolower(trim($slug, '/'));
        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return 'projects/' . $slug . '/share-qr.png';
        }

        return $slug . '/share-qr.png';
    }
}

if (! function_exists('project_share_qr_page_path')) {
    function project_share_qr_page_path(string $slug): string
    {
        $slug = strtolower(trim($slug, '/'));
        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return 'projects/' . $slug . '/share';
        }

        return $slug . '/share';
    }
}

if (! function_exists('project_share_qr_absolute_url')) {
    function project_share_qr_absolute_url(string $relativePath): string
    {
        helper('locale');
        $url = localized_site_url($relativePath);
        if (str_starts_with($url, '/')) {
            $url = rtrim((string) base_url(), '/') . $url;
        }

        return $url;
    }
}

if (! function_exists('project_share_qr_image_url')) {
    function project_share_qr_image_url(string $slug): string
    {
        $url = project_share_qr_absolute_url(project_share_qr_image_path($slug));
        helper('asset');
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . rawurlencode(front_asset_version());
    }
}

if (! function_exists('project_share_qr_page_url')) {
    function project_share_qr_page_url(string $slug): string
    {
        return project_share_qr_absolute_url(project_share_qr_page_path($slug));
    }
}

if (! function_exists('project_share_social_links')) {
    /**
     * Liens de partage.
     *
     * Facebook / LinkedIn / X : URL web (sharer) — le JS mobile utilise Web Share API ou onglet navigateur.
     * WhatsApp : deep link app + texte avec l’URL du projet.
     * TikTok : pas d’URL de partage officielle — copie + ouverture app (JS).
     *
     * @return array<string, array{web: string, mobile?: string, app?: string, android?: string}>
     */
    function project_share_social_links(string $title, string $projectPageUrl, string $shareQrPageUrl): array
    {
        $projectEnc = rawurlencode($projectPageUrl);
        $waText     = rawurlencode($title . ' — ' . $projectPageUrl);
        $tweetText  = rawurlencode(lang('Projects.share_qr_social_text', ['title' => $title]));
        $fbWeb      = 'https://www.facebook.com/sharer/sharer.php?u=' . $projectEnc;
        $liWeb      = 'https://www.linkedin.com/shareArticle?mini=true&url=' . $projectEnc;
        $xWeb       = 'https://twitter.com/intent/tweet?url=' . $projectEnc . '&text=' . $tweetText;

        return [
            'facebook' => [
                'web'     => $fbWeb,
                'mobile'  => $fbWeb,
                'app'     => 'fb://facewebmodal/f?href=' . rawurlencode($fbWeb),
                'android' => 'intent://www.facebook.com/sharer.php?u=' . $projectEnc . '#Intent;scheme=https;package=com.facebook.katana;end',
            ],
            'whatsapp' => [
                'web'     => 'https://api.whatsapp.com/send?text=' . $waText,
                'app'     => 'whatsapp://send?text=' . $waText,
                'android' => 'intent://send?text=' . $waText . '#Intent;scheme=whatsapp;package=com.whatsapp;end',
            ],
            'linkedin' => [
                'web'     => $liWeb,
                'mobile'  => $liWeb,
                'app'     => 'linkedin://shareArticle?mini=true&url=' . $projectEnc,
                'android' => 'intent://www.linkedin.com/shareArticle?mini=true&url=' . $projectEnc . '#Intent;scheme=https;package=com.linkedin.android;end',
            ],
            'x' => [
                'web'    => $xWeb,
                'mobile' => $xWeb,
                'app'    => 'twitter://post?message=' . rawurlencode($title . ' ' . $projectPageUrl),
            ],
            'tiktok' => [
                'web' => '',
                'app' => 'snssdk1233://',
            ],
            'email' => [
                'web' => 'mailto:?subject=' . rawurlencode(lang('Projects.share_qr_email_subject', ['title' => $title]))
                    . '&body=' . rawurlencode(lang('Projects.share_qr_email_body', ['title' => $title, 'url' => $projectPageUrl])),
            ],
        ];
    }
}

if (! function_exists('project_public_absolute_url')) {
    function project_public_absolute_url(string $slug): string
    {
        $url = project_public_url($slug);
        if (str_starts_with($url, '/')) {
            return rtrim((string) base_url(), '/') . $url;
        }

        return $url;
    }
}

if (! function_exists('projects_program_filter_post_url')) {
    /**
     * URL POST du filtre liste projets (AJAX), selon préfixe /projects ou vhost dédié.
     */
    function projects_program_filter_post_url(): string
    {
        helper('locale');
        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return localized_site_url('projects/filter');
        }

        return localized_site_url('filter');
    }
}

if (! function_exists('project_fund_validation_messages')) {
    /**
     * Messages de validation localisés — formulaire financement projet.
     *
     * @param 'budget'|'material' $type
     *
     * @return array<string, array<string, string>>
     */
    function project_fund_validation_messages(string $type): array
    {
        helper('language');
        $name = [
            'required'   => lang('Projects.fund_validation_name_required'),
            'max_length' => lang('Projects.fund_validation_name_max'),
        ];
        $phoneCountry = [
            'required'    => lang('Site.join_phone_country_required'),
            'regex_match' => lang('Site.join_phone_country_required'),
        ];
        $phoneNumber = [
            'required'    => lang('Projects.fund_validation_phone_required'),
            'max_length'  => lang('Projects.fund_validation_phone_max'),
            'regex_match' => lang('Site.join_phone_invalid'),
        ];
        $donorEmail = [
            'valid_email' => lang('Projects.fund_validation_email_invalid'),
            'max_length'  => lang('Projects.fund_validation_email_invalid'),
        ];
        $prefix = $type === 'material' ? 'material' : 'budget';

        if ($type === 'material') {
            return [
                'material_donor_name'      => $name,
                $prefix . '_phone_country' => $phoneCountry,
                $prefix . '_phone_number'  => $phoneNumber,
                'material_donor_email'     => $donorEmail,
                'material_pickup_location' => ['max_length' => lang('Projects.fund_validation_pickup_max')],
                'material_remarks'         => ['max_length' => lang('Projects.fund_validation_remarks_max')],
            ];
        }

        return [
            'budget_donor_name'      => $name,
            $prefix . '_phone_country' => $phoneCountry,
            $prefix . '_phone_number'  => $phoneNumber,
            'budget_donor_email'       => $donorEmail,
            'budget_amount'            => [
                'required'    => lang('Projects.fund_validation_amount_required'),
                'regex_match' => lang('Projects.fund_validation_amount_invalid'),
                'max_length'  => lang('Projects.fund_validation_amount_max'),
            ],
            'budget_remarks'           => ['max_length' => lang('Projects.fund_validation_remarks_max')],
        ];
    }
}

if (! function_exists('project_fund_material_lines_from_old_input')) {
    /**
     * Lignes article / quantité pour réaffichage du formulaire (old input).
     *
     * @return list<array{item: string, qty: string}>
     */
    function project_fund_material_lines_from_old_input(): array
    {
        $names = old('material_item_name');
        $qtys  = old('material_item_qty');
        if (is_array($names)) {
            $lines = [];
            $count = max(count($names), is_array($qtys) ? count($qtys) : 0);
            for ($i = 0; $i < $count; $i++) {
                $item = trim((string) ($names[$i] ?? ''));
                $qty  = trim((string) (is_array($qtys) ? ($qtys[$i] ?? '') : ''));
                if ($item === '' && $qty === '') {
                    continue;
                }
                $lines[] = ['item' => $item, 'qty' => $qty];
            }

            return $lines !== [] ? $lines : [['item' => '', 'qty' => '']];
        }

        $legacyItem = trim((string) old('material_items', old('items', '')));
        $legacyQty  = trim((string) old('material_quantity', old('quantity', '')));
        if ($legacyItem !== '') {
            return [['item' => $legacyItem, 'qty' => $legacyQty]];
        }

        return [['item' => '', 'qty' => '']];
    }
}

if (! function_exists('project_fund_material_lines_from_request')) {
    /**
     * @return list<array{item: string, qty: string}>
     */
    function project_fund_material_lines_from_request(): array
    {
        $names = service('request')->getPost('material_item_name');
        $qtys  = service('request')->getPost('material_item_qty');
        if (! is_array($names)) {
            $names = [];
        }
        if (! is_array($qtys)) {
            $qtys = [];
        }

        $lines = [];
        $count = max(count($names), count($qtys));
        for ($i = 0; $i < $count; $i++) {
            $item = trim((string) ($names[$i] ?? ''));
            $qty  = trim((string) ($qtys[$i] ?? ''));
            if ($item === '' && $qty === '') {
                continue;
            }
            $lines[] = ['item' => $item, 'qty' => $qty];
        }

        return $lines;
    }
}

if (! function_exists('project_fund_validate_material_lines')) {
    /**
     * @param list<array{item: string, qty: string}> $lines
     *
     * @return array<string, string>
     */
    function project_fund_validate_material_lines(array $lines): array
    {
        helper('language');
        if ($lines === []) {
            return ['material_items' => lang('Projects.fund_validation_items_required')];
        }

        foreach ($lines as $line) {
            $item = $line['item'];
            $qty  = $line['qty'];
            if ($item === '') {
                return ['material_items' => lang('Projects.fund_validation_items_required')];
            }
            if (mb_strlen($item) > 255) {
                return ['material_items' => lang('Projects.fund_validation_items_max')];
            }
            if ($qty === '') {
                return ['material_quantity' => lang('Projects.fund_validation_quantity_required')];
            }
            if (mb_strlen($qty) > 120) {
                return ['material_quantity' => lang('Projects.fund_validation_quantity_max')];
            }
        }

        return [];
    }
}

if (! function_exists('project_fund_material_storage_from_lines')) {
    /**
     * @param list<array{item: string, qty: string}> $lines
     *
     * @return array{items: string, quantity: string|null}
     */
    function project_fund_material_storage_from_lines(array $lines): array
    {
        $itemLines = [];
        $qtyParts  = [];
        foreach ($lines as $line) {
            $item = $line['item'];
            $qty  = $line['qty'];
            $itemLines[] = $qty !== '' ? $item . ' — ×' . $qty : $item;
            if ($qty !== '') {
                $qtyParts[] = $qty;
            }
        }

        return [
            'items'    => implode("\n", $itemLines),
            'quantity' => $qtyParts !== [] ? implode(', ', $qtyParts) : null,
        ];
    }
}

if (! function_exists('project_published_material_parse_line')) {
    /**
     * @return array{item: string, qty: string}|null
     */
    function project_published_material_parse_line(string $chunk): ?array
    {
        $chunk = trim($chunk);
        if ($chunk === '') {
            return null;
        }
        if (preg_match('/^(.+?)\s*[—–-]\s*[×x]\s*(.+)$/u', $chunk, $m)) {
            return ['item' => trim($m[1]), 'qty' => trim($m[2])];
        }

        return ['item' => $chunk, 'qty' => ''];
    }
}

if (! function_exists('project_published_material_split_items_string')) {
    /**
     * @return list<string>
     */
    function project_published_material_split_items_string(string $items): array
    {
        $items = trim($items);
        if ($items === '') {
            return [];
        }
        if (preg_match('/\R/u', $items)) {
            $chunks = preg_split('/\R+/u', $items) ?: [];

            return array_values(array_filter(array_map('trim', $chunks), static fn (string $c): bool => $c !== ''));
        }
        if (preg_match_all('/\s*[—–-]\s*[×x]\s*/u', $items) > 1) {
            $parts = preg_split('/(?=\s+[^\s—–-][^—\n]*?\s*[—–-]\s*[×x]\s*)/u', $items) ?: [];
            if (count($parts) > 1) {
                return array_values(array_filter(array_map('trim', $parts), static fn (string $c): bool => $c !== ''));
            }
        }

        return [$items];
    }
}

if (! function_exists('project_published_material_lines_from_row')) {
    /**
     * @param array<string, mixed> $row
     *
     * @return list<array{item: string, qty: string}>
     */
    function project_published_material_lines_from_row(array $row): array
    {
        $items = trim((string) ($row['items'] ?? ''));
        if ($items === '') {
            return [];
        }

        $lines = [];
        foreach (project_published_material_split_items_string($items) as $chunk) {
            $parsed = project_published_material_parse_line($chunk);
            if ($parsed !== null) {
                $lines[] = $parsed;
            }
        }

        if ($lines === []) {
            return [];
        }

        if (count($lines) === 1 && $lines[0]['qty'] === '') {
            $qty = trim((string) ($row['quantity'] ?? ''));
            if ($qty !== '' && ! str_contains($qty, ',')) {
                $lines[0]['qty'] = $qty;
            }
        }

        return $lines;
    }
}

if (! function_exists('project_published_amount_display')) {
    function project_published_amount_display(string $amount, string $locale): string
    {
        $amount = trim($amount);
        if ($amount === '') {
            return '';
        }
        $compact = preg_replace('/\s+/u', '', $amount) ?? $amount;
        if ($compact !== '' && preg_match('/^\d+([.,]\d+)?$/', $compact)) {
            $num = (float) str_replace(',', '.', $compact);
            $dec = fmod($num, 1.0) === 0.0 ? 0 : 2;
            if ($locale === 'en') {
                return number_format($num, $dec, '.', ',');
            }

            return number_format($num, $dec, ',', ' ');
        }

        return $amount;
    }
}

if (! function_exists('project_fund_phone_contact_from_request')) {
    /**
     * Téléphone formaté (indicatif + numéro) depuis la requête POST.
     */
    function project_fund_phone_contact_from_request(string $prefix): string
    {
        $country = trim((string) service('request')->getPost($prefix . '_phone_country'));
        $number  = trim((string) service('request')->getPost($prefix . '_phone_number'));

        if ($number === '') {
            return '';
        }

        return $country !== '' ? trim($country . ' ' . $number) : $number;
    }
}

if (! function_exists('project_fund_post_url')) {
    /**
     * URL POST du formulaire « Financer ce projet ».
     */
    function project_fund_post_url(string $slug): string
    {
        helper('locale');
        $slug = trim($slug, '/');
        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return localized_site_url('projects/' . $slug . '/fund');
        }

        return localized_site_url($slug . '/fund');
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

if (! function_exists('project_format_ariary_table_cell')) {
    function project_format_ariary_table_cell(float $ariary, string $locale): string
    {
        if ($locale === 'en') {
            return number_format($ariary, 0, '.', ',');
        }

        return number_format($ariary, 0, ',', ' ');
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

if (! function_exists('project_body_blocks_list')) {
    /**
     * @param array<string, mixed> $project
     *
     * @return list<array<string, mixed>>
     */
    function project_body_blocks_list(array $project): array
    {
        if (project_body_content_mode($project) !== 'blocks') {
            return [];
        }
        $raw = $project['body_blocks'] ?? null;
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode((string) $raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}

if (! function_exists('project_has_financial_funding')) {
    /**
     * Budget carte ou bloc budget_table avec lignes.
     *
     * @param array<string, mixed> $project
     */
    function project_has_financial_funding(array $project): bool
    {
        if (trim((string) ($project['budget_display'] ?? '')) !== '') {
            return true;
        }
        foreach (project_body_blocks_list($project) as $block) {
            if (! is_array($block) || (string) ($block['type'] ?? '') !== 'budget_table') {
                continue;
            }
            $rows = $block['rows'] ?? [];
            if (is_array($rows) && $rows !== []) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('project_has_material_needs')) {
    /**
     * @param array<string, mixed> $project
     */
    function project_has_material_needs(array $project): bool
    {
        foreach (project_body_blocks_list($project) as $block) {
            if (! is_array($block) || (string) ($block['type'] ?? '') !== 'material_needs') {
                continue;
            }
            $rows = $block['rows'] ?? [];
            if (is_array($rows) && $rows !== []) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('project_fund_mailto_url')) {
    /**
     * @param array<string, mixed> $project
     * @param 'budget'|'material' $kind
     */
    function project_fund_mailto_url(array $project, string $kind): string
    {
        $title = trim((string) ($project['title'] ?? ''));
        if ($kind === 'material') {
            $subject = 'Matériel — ' . $title;
            $body    = "Bonjour,\n\nJe souhaite apporter du matériel pour le projet : " . $title . ".\n\n";
            foreach (project_body_blocks_list($project) as $block) {
                if (! is_array($block) || (string) ($block['type'] ?? '') !== 'material_needs') {
                    continue;
                }
                foreach ($block['rows'] ?? [] as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $item = trim((string) ($row['item'] ?? ''));
                    if ($item === '') {
                        continue;
                    }
                    $qty = trim((string) ($row['quantity'] ?? ''));
                    $body .= '• ' . $item;
                    if ($qty !== '') {
                        $body .= ' (' . $qty . ')';
                    }
                    $body .= "\n";
                }
            }
            $body .= "\nMerci.";

            return 'mailto:partnerships@govgenz.org?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($body);
        }

        $subject = 'Financement — ' . $title;
        $budget  = trim((string) ($project['budget_display'] ?? ''));

        return 'mailto:partnerships@govgenz.org?subject=' . rawurlencode($subject)
            . ($budget !== '' ? '&body=' . rawurlencode("Budget indicatif : {$budget}\n\n") : '');
    }
}

if (! function_exists('project_geography_front_display')) {
    /**
     * Géographie structurée pour le front : niveau le plus fin, séparateur « & », max 3 + survol.
     *
     * @param array<string, mixed> $project
     *
     * @return array{text: string, html: string}
     */
    function project_geography_front_display(array $project): array
    {
        return \App\Libraries\ProjectGeographyPayload::frontDisplayFromProject($project);
    }
}
