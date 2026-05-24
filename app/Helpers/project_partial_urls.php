<?php

declare(strict_types=1);

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

if (! function_exists('project_share_qr_page_url')) {
    function project_share_qr_page_url(string $slug): string
    {
        return project_share_qr_absolute_url(project_share_qr_page_path($slug));
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

