<?php

declare(strict_types=1);

/**
 * URLs publiques et admin (helper('admin') charge ce fichier).
 */
if (! function_exists('admin_site_url_for_locale')) {
    /**
     * URL publique du site principal pour un chemin relatif et une locale explicite
     * (l’admin ne doit pas dépendre de SiteContext::locale(), souvent « fr »).
     */
    function admin_site_url_for_locale(string $relativePath = '', ?string $locale = null): string
    {
        helper('url');

        $locale       = $locale === 'en' ? 'en' : 'fr';
        $relativePath = ltrim($relativePath, '/');

        if ($locale === 'en') {
            return $relativePath === '' ? site_url('en') : site_url('en/' . $relativePath);
        }

        return $relativePath === '' ? site_url('/') : site_url($relativePath);
    }
}

if (! function_exists('admin_public_page_url')) {
    /**
     * URL publique connue pour une page CMS (slug + locale), sinon null.
     * Bandeaux de listes (press, projects, positions) → URL de la liste, pas une page CMS isolée.
     */
    function admin_public_page_url(?string $slug, ?string $locale = null): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        helper(['url', 'cms']);

        $locale ??= 'fr';
        $locale = $locale === 'en' ? 'en' : 'fr';

        $listKind = cms_list_hero_page_kind($slug);
        if ($listKind !== null) {
            return match ($listKind) {
                'press'     => admin_public_press_list_url($locale),
                'projects'  => admin_public_projects_list_url($locale),
                'positions' => admin_public_positions_list_url($locale),
                default     => null,
            };
        }

        if ($slug === 'home') {
            return $locale === 'en' ? site_url('en') : site_url('/');
        }

        return $locale === 'en' ? site_url('en/' . $slug) : site_url($slug);
    }
}

if (! function_exists('admin_public_press_url')) {
    /**
     * URL publique pour un article presse publié (slug + locale).
     */
    function admin_public_press_url(?string $slug, ?string $locale = null): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        helper('url');

        $locale ??= 'fr';

        return $locale === 'en' ? site_url('en/press/' . $slug) : site_url('press/' . $slug);
    }
}

if (! function_exists('admin_public_projects_list_url')) {
    /**
     * URL publique de la liste des projets (grille / filtres), pas la page CMS « hero ».
     * Préfixe /projects sur le site principal ; vhost projets (sans préfixe) si .env configuré.
     */
    function admin_public_projects_list_url(?string $locale = null): string
    {
        helper('url');
        $locale = $locale === 'en' ? 'en' : 'fr';

        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return admin_site_url_for_locale('projects', $locale);
        }

        $projectsBase = trim((string) env('app.projectsBaseURL', ''));
        if ($projectsBase !== '' && filter_var($projectsBase, FILTER_VALIDATE_URL)) {
            $root = rtrim($projectsBase, '/');

            return $locale === 'en' ? $root . '/en/' : $root . '/';
        }

        $host = trim((string) env('app.projectsHost', ''));
        if ($host === '') {
            return $locale === 'en' ? site_url('en/projects') : site_url('projects');
        }

        $cfg    = config('App');
        $scheme = $cfg->forceGlobalSecureRequests ? 'https' : (parse_url((string) $cfg->baseURL, PHP_URL_SCHEME) ?: 'http');
        $path   = $locale === 'en' ? '/en/' : '/';

        return $scheme . '://' . $host . $path;
    }
}

if (! function_exists('admin_public_press_list_url')) {
    function admin_public_press_list_url(?string $locale = null): string
    {
        helper('url');
        $locale = $locale === 'en' ? 'en' : 'fr';

        return $locale === 'en' ? site_url('en/press') : site_url('press');
    }
}

if (! function_exists('admin_public_positions_list_url')) {
    function admin_public_positions_list_url(?string $locale = null): string
    {
        helper('url');
        $locale = $locale === 'en' ? 'en' : 'fr';

        if (\App\Libraries\SiteContext::positionsPathPrefixEnabled()) {
            return admin_site_url_for_locale('positions', $locale);
        }

        $positionsBase = trim((string) env('app.positionsBaseURL', ''));
        if ($positionsBase !== '' && filter_var($positionsBase, FILTER_VALIDATE_URL)) {
            $root = rtrim($positionsBase, '/');

            return $locale === 'en' ? $root . '/en/' : $root . '/';
        }

        $host = trim((string) env('app.positionsHost', ''));
        if ($host === '') {
            return $locale === 'en' ? site_url('en/positions') : site_url('positions');
        }

        $cfg    = config('App');
        $scheme = $cfg->forceGlobalSecureRequests ? 'https' : (parse_url((string) $cfg->baseURL, PHP_URL_SCHEME) ?: 'http');
        $path   = $locale === 'en' ? '/en/' : '/';

        return $scheme . '://' . $host . $path;
    }
}

if (! function_exists('admin_public_project_url')) {
    /**
     * URL publique d’une fiche projet publiée (slug + locale), sinon null.
     */
    function admin_public_project_url(?string $slug, ?string $locale = null): ?string
    {
        if ($slug === null || trim($slug) === '') {
            return null;
        }

        $locale = $locale === 'en' ? 'en' : 'fr';
        $slug   = strtolower(trim($slug, '/'));
        if ($slug === '') {
            return admin_public_projects_list_url($locale);
        }

        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return admin_site_url_for_locale('projects/' . $slug, $locale);
        }

        $projectsBase = trim((string) env('app.projectsBaseURL', ''));
        if ($projectsBase !== '' && filter_var($projectsBase, FILTER_VALIDATE_URL)) {
            $root = rtrim($projectsBase, '/');
            $path = $locale === 'en' ? '/en/' . $slug : '/' . $slug;

            return $root . $path;
        }

        $host = trim((string) env('app.projectsHost', ''));
        if ($host === '') {
            return admin_site_url_for_locale($slug, $locale);
        }

        $cfg    = config('App');
        $scheme = $cfg->forceGlobalSecureRequests ? 'https' : (parse_url((string) $cfg->baseURL, PHP_URL_SCHEME) ?: 'http');
        $path   = $locale === 'en' ? '/en/' . $slug : '/' . $slug;

        return $scheme . '://' . $host . $path;
    }
}

if (! function_exists('admin_public_position_url')) {
    /**
     * URL publique d’une fiche position publiée (slug + locale), sinon null.
     */
    function admin_public_position_url(?string $slug, ?string $locale = null): ?string
    {
        if ($slug === null || trim($slug) === '') {
            return null;
        }

        $locale = $locale === 'en' ? 'en' : 'fr';
        $slug   = strtolower(trim($slug, '/'));
        if ($slug === '') {
            return admin_public_positions_list_url($locale);
        }

        if (\App\Libraries\SiteContext::positionsPathPrefixEnabled()) {
            return admin_site_url_for_locale('positions/' . $slug, $locale);
        }

        $positionsBase = trim((string) env('app.positionsBaseURL', ''));
        if ($positionsBase !== '' && filter_var($positionsBase, FILTER_VALIDATE_URL)) {
            $root = rtrim($positionsBase, '/');
            $path = $locale === 'en' ? '/en/' . $slug : '/' . $slug;

            return $root . $path;
        }

        $host = trim((string) env('app.positionsHost', ''));
        if ($host === '') {
            return admin_site_url_for_locale($slug, $locale);
        }

        $cfg    = config('App');
        $scheme = $cfg->forceGlobalSecureRequests ? 'https' : (parse_url((string) $cfg->baseURL, PHP_URL_SCHEME) ?: 'http');
        $path   = $locale === 'en' ? '/en/' . $slug : '/' . $slug;

        return $scheme . '://' . $host . $path;
    }
}
