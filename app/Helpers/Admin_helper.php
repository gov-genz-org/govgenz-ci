<?php

declare(strict_types=1);

/**
 * Utilitaires vues admin (charger avec helper('admin')).
 */
if (! function_exists('admin_public_page_url')) {
    /**
     * URL publique connue pour une page CMS (slug + locale), sinon null.
     */
    function admin_public_page_url(?string $slug, ?string $locale = null): ?string
    {
        if ($slug === null || $slug === '') {
            return null;
        }

        helper('url');

        $locale ??= 'fr';

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
