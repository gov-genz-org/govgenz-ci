<?php

declare(strict_types=1);

use App\Controllers\Front\Join;
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
        helper(['url', 'cms']);
        $loc      = SiteContext::locale();
        $segments = SiteContext::publicUriSegments();

        if (! SiteContext::isProjectsSite()
            && ! SiteContext::isPositionsSite()
            && count($segments) === 1) {
            $partnerSlug = cms_page_partner_slug_for_locale_switch((string) $segments[0], $loc);
            if ($partnerSlug !== null) {
                return $loc === 'fr'
                    ? site_url('en/' . $partnerSlug)
                    : site_url($partnerSlug);
            }
        }

        // Mini-site « /projects » sur le domaine principal : les segments publics sont déjà sans
        // le préfixe « projects » (SiteContextFilter). Sans traitement dédié, [] → /en ou / au lieu
        // de /en/projects ou /projects.
        if (SiteContext::isProjectsSite() && SiteContext::projectsPathPrefixEnabled()) {
            if ($loc === 'fr') {
                $mapped = array_map(
                    static fn (string $s): string => locale_slug_fr_to_en($s),
                    $segments,
                );
                $tail = implode('/', $mapped);

                return $tail === '' ? site_url('en/projects') : site_url('en/projects/' . $tail);
            }

            $mapped = array_map(
                static fn (string $s): string => locale_slug_en_to_fr($s),
                $segments,
            );
            $tail = implode('/', $mapped);

            return $tail === '' ? site_url('projects') : site_url('projects/' . $tail);
        }

        // Vhost projets (sans /projects dans l’URL) : site_url() peut viser le mauvais domaine si
        // app.projectsBaseURL n’est pas défini — on garde le même hôte que la requête courante.
        if (SiteContext::isProjectsSite()
            && ! SiteContext::projectsPathPrefixEnabled()
            && SiteContext::httpHostMatchesProjectsHost(service('request'))) {
            $u      = service('request')->getUri();
            $origin = $u->getScheme() . '://' . $u->getHost();
            $port   = $u->getPort();
            if ($port !== null && ! in_array((int) $port, [80, 443], true)) {
                $origin .= ':' . $port;
            }
            if ($loc === 'fr') {
                $mapped = array_map(
                    static fn (string $s): string => locale_slug_fr_to_en($s),
                    $segments,
                );
                $tail = implode('/', $mapped);

                return $tail === '' ? $origin . '/en/' : $origin . '/en/' . $tail;
            }

            $mapped = array_map(
                static fn (string $s): string => locale_slug_en_to_fr($s),
                $segments,
            );
            $tail = implode('/', $mapped);

            return $tail === '' ? $origin . '/' : $origin . '/' . $tail;
        }

        if (SiteContext::isPositionsSite() && SiteContext::positionsPathPrefixEnabled()) {
            helper('position');

            $mapPositionSegments = static function (array $segs, string $fromLocale): array {
                $out = [];
                foreach ($segs as $s) {
                    $s = trim((string) $s);
                    if ($s === '' || $s === 'filter') {
                        continue;
                    }
                    $partner = position_partner_slug_for_locale_switch($s, $fromLocale);
                    if ($partner !== null) {
                        $out[] = $partner;

                        continue;
                    }
                    $out[] = $fromLocale === 'fr'
                        ? locale_slug_fr_to_en($s)
                        : locale_slug_en_to_fr($s);
                }

                return $out;
            };

            if ($loc === 'fr') {
                $mapped = $mapPositionSegments($segments, $loc);
                $tail   = implode('/', $mapped);

                return $tail === '' ? site_url('en/positions') : site_url('en/positions/' . $tail);
            }

            $mapped = $mapPositionSegments($segments, $loc);
            $tail   = implode('/', $mapped);

            return $tail === '' ? site_url('positions') : site_url('positions/' . $tail);
        }

        if (SiteContext::isPositionsSite()
            && ! SiteContext::positionsPathPrefixEnabled()
            && SiteContext::httpHostMatchesPositionsHost(service('request'))) {
            helper('position');
            $u      = service('request')->getUri();
            $origin = $u->getScheme() . '://' . $u->getHost();
            $port   = $u->getPort();
            if ($port !== null && ! in_array((int) $port, [80, 443], true)) {
                $origin .= ':' . $port;
            }

            $mapPositionSegments = static function (array $segs, string $fromLocale): array {
                $out = [];
                foreach ($segs as $s) {
                    $s = trim((string) $s);
                    if ($s === '' || $s === 'filter') {
                        continue;
                    }
                    $partner = position_partner_slug_for_locale_switch($s, $fromLocale);
                    if ($partner !== null) {
                        $out[] = $partner;

                        continue;
                    }
                    $out[] = $fromLocale === 'fr'
                        ? locale_slug_fr_to_en($s)
                        : locale_slug_en_to_fr($s);
                }

                return $out;
            };

            if ($loc === 'fr') {
                $mapped = $mapPositionSegments($segments, $loc);
                $tail   = implode('/', $mapped);

                return $tail === '' ? $origin . '/en/' : $origin . '/en/' . $tail;
            }

            $mapped = $mapPositionSegments($segments, $loc);
            $tail   = implode('/', $mapped);

            return $tail === '' ? $origin . '/' : $origin . '/' . $tail;
        }

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

if (! function_exists('public_join_url')) {
    /**
     * URL du formulaire Rejoindre sur le site principal, avec secteurs pré-sélectionnés.
     *
     * @param list<string>         $sectorKeys  codes secteur (ex. depuis sectors_csv d’un projet)
     * @param array<string, mixed> $extraQuery paramètres GET additionnels
     */
    function public_join_url(array $sectorKeys = [], array $extraQuery = []): string
    {
        helper('url');
        $locale = SiteContext::locale();

        if (SiteContext::isProjectsSite() && ! SiteContext::projectsPathPrefixEnabled()) {
            $mainBase = rtrim(trim((string) env('app.baseURL', '')), '/ ');
            if ($mainBase === '' || filter_var($mainBase, FILTER_VALIDATE_URL) === false) {
                $mainBase = rtrim((string) config('App')->baseURL, '/ ');
            }
            $path = $locale === 'en' ? '/en/join' : '/join';
            $base = $mainBase . $path;
        } else {
            $base = localized_site_url('join');
        }

        $sectorKeys = Join::normalizeSectorKeys($sectorKeys);
        if ($sectorKeys === [] && $extraQuery === []) {
            return $base;
        }

        $query = $extraQuery;
        foreach ($sectorKeys as $code) {
            $query['sector'][] = $code;
        }

        return $base . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}
