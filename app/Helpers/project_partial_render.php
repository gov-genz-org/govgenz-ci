<?php

declare(strict_types=1);

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

if (! function_exists('project_public_asset_url')) {
    function project_public_asset_url(string $relativePublicPath): string
    {
        helper('asset');

        return public_asset_url($relativePublicPath);
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

