<?php

declare(strict_types=1);

use App\Libraries\SiteContext;
use Config\Analytics;

if (! function_exists('analytics_config')) {
    function analytics_config(): Analytics
    {
        return config(Analytics::class);
    }
}

if (! function_exists('analytics_is_active')) {
    /** Site public + config activée + ID GA4 renseigné. */
    function analytics_is_active(): bool
    {
        if (is_cli()) {
            return false;
        }

        $path = trim(service('request')->getPath(), '/');
        if ($path === 'admin' || str_starts_with($path, 'admin/')) {
            return false;
        }

        $cfg = analytics_config();

        return $cfg->enabled && preg_match('/^G-[A-Z0-9]+$/i', $cfg->ga4MeasurementId) === 1;
    }
}

if (! function_exists('analytics_privacy_url')) {
    function analytics_privacy_url(): ?string
    {
        if (! analytics_is_active()) {
            return null;
        }

        $slug = trim(analytics_config()->privacyPageSlug);
        if ($slug === '') {
            return null;
        }

        helper('locale');

        return localized_site_url($slug);
    }
}
