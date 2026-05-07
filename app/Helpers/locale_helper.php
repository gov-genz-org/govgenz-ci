<?php

declare(strict_types=1);

use App\Libraries\SiteContext;

if (! function_exists('localized_site_url')) {
    /**
     * URL publique avec préfixe /en lorsque la locale active est l’anglais.
     */
    function localized_site_url(string $relativePath = ''): string
    {
        helper('url');
        $relativePath = ltrim($relativePath, '/');
        $locale       = SiteContext::locale();

        if ($locale === 'en') {
            return $relativePath === '' ? site_url('en') : site_url('en/' . $relativePath);
        }

        return $relativePath === '' ? site_url('/') : site_url($relativePath);
    }
}

if (! function_exists('locale_slug_fr_to_en')) {
    function locale_slug_fr_to_en(string $segment): string
    {
        return match ($segment) {
            'qui-sommes-nous' => 'who-we-are',
            'notre-adn'       => 'our-dna',
            'secteurs'        => 'sectors',
            'etude'           => 'study',
            default           => $segment,
        };
    }
}

if (! function_exists('locale_slug_en_to_fr')) {
    function locale_slug_en_to_fr(string $segment): string
    {
        return match ($segment) {
            'who-we-are' => 'qui-sommes-nous',
            'our-dna'    => 'notre-adn',
            'sectors'    => 'secteurs',
            'study'      => 'etude',
            default      => $segment,
        };
    }
}

if (! function_exists('localized_slug_from_fr')) {
    /**
     * Segmente d’URL canonique FR adapté à la locale courante (ex. pied de page).
     */
    function localized_slug_from_fr(string $frSlug): string
    {
        if (SiteContext::locale() === 'en') {
            return locale_slug_fr_to_en($frSlug);
        }

        return $frSlug;
    }
}

if (! function_exists('locale_switch_url')) {
    /**
     * Lien vers l’équivalent approximatif de la page courante dans l’autre langue.
     */
    function locale_switch_url(): string
    {
        helper('url');
        $loc      = SiteContext::locale();
        $segments = SiteContext::publicUriSegments();

        if ($loc === 'fr') {
            $mapped = array_map(
                static fn (string $s): string => locale_slug_fr_to_en($s),
                $segments,
            );
            $path = implode('/', $mapped);

            return $path === '' ? site_url('en') : site_url('en/' . $path);
        }

        $mapped = array_map(
            static fn (string $s): string => locale_slug_en_to_fr($s),
            $segments,
        );
        $path = implode('/', $mapped);

        return $path === '' ? site_url('/') : site_url($path);
    }
}
