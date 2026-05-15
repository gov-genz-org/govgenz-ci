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

if (! function_exists('admin_public_projects_program_list_url')) {
    /**
     * URL publique de la liste des projets (grille / filtres), pas la page CMS « hero ».
     * Préfixe /projects sur le site principal ; vhost projets (sans préfixe) si .env configuré.
     */
    function admin_public_projects_program_list_url(?string $locale = null): string
    {
        helper('url');
        $locale = $locale === 'en' ? 'en' : 'fr';

        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return $locale === 'en' ? site_url('en/projects') : site_url('projects');
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

        $cfg = config('App');
        $scheme = $cfg->forceGlobalSecureRequests ? 'https' : (parse_url((string) $cfg->baseURL, PHP_URL_SCHEME) ?: 'http');
        $path    = $locale === 'en' ? '/en/' : '/';

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

        helper(['project', 'url']);

        return project_public_url(trim($slug));
    }
}

if (! function_exists('admin_staff_is_editor_only')) {
    /** Modérateur / rédacteur : pas admin technique. */
    function admin_staff_is_editor_only(): bool
    {
        return session()->get('staff_role') === 'editor';
    }
}

if (! function_exists('admin_list_sort_url')) {
    /**
     * URL de liste admin en conservant filtres GET ; bascule asc/desc sur la colonne active.
     */
    function admin_list_sort_url(string $column, string $currentSort, string $currentDir): string
    {
        helper('url');

        $params = service('request')->getGet();
        if (! is_array($params)) {
            $params = [];
        }

        if ($currentSort === $column) {
            $params['dir'] = strtolower($currentDir) === 'asc' ? 'desc' : 'asc';
        } else {
            $params['sort'] = $column;
            $params['dir']  = 'asc';
        }

        unset($params['page']);

        $path = ltrim((string) service('request')->getUri()->getPath(), '/');
        $base = site_url($path);
        $qs   = http_build_query($params);

        return $qs !== '' ? $base . '?' . $qs : $base;
    }
}

if (! function_exists('admin_list_sort_th')) {
    /** En-tête de colonne triable pour les tableaux admin. */
    function admin_list_sort_th(string $column, string $label, string $sort, string $dir): string
    {
        $active = $sort === $column;
        $arrow  = '';
        if ($active) {
            $glyph = strtolower($dir) === 'asc' ? '▲' : '▼';
            $arrow = ' <span class="admin-sort-arrow" aria-hidden="true">' . $glyph . '</span>';
        }

        $url = admin_list_sort_url($column, $sort, $dir);
        $cls = 'admin-sort-link text-decoration-none';
        if ($active) {
            $cls .= ' admin-sort-link--active fw-semibold';
        }

        $aria = $active
            ? ' aria-sort="' . (strtolower($dir) === 'asc' ? 'ascending' : 'descending') . '"'
            : '';

        return '<a href="' . esc($url, 'attr') . '" class="' . esc($cls, 'attr') . '"' . $aria . '>'
            . esc($label) . $arrow . '</a>';
    }
}

if (! function_exists('admin_list_sort_hidden_fields')) {
    /** Conserve le tri lors d’un formulaire GET de filtre. */
    function admin_list_sort_hidden_fields(string $sort, string $dir): string
    {
        return '<input type="hidden" name="sort" value="' . esc($sort, 'attr') . '">'
            . '<input type="hidden" name="dir" value="' . esc(strtolower($dir), 'attr') . '">';
    }
}

if (! function_exists('admin_datetime_storage_timezone')) {
    /** Fuseau supposé en base pour les DATETIME sans offset. */
    function admin_datetime_storage_timezone(): string
    {
        $tz = trim((string) env('app.datetimeStorageTimezone', 'UTC'));

        return $tz !== '' ? $tz : 'UTC';
    }
}

if (! function_exists('admin_client_timezone')) {
    /**
     * Fuseau du navigateur (cookie posé par js/admin/datetime-client.js), sinon UTC.
     */
    function admin_client_timezone(): string
    {
        $tz = trim((string) ($_COOKIE['admin_client_tz'] ?? ''));
        if ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) {
            return $tz;
        }

        return 'UTC';
    }
}

if (! function_exists('admin_datetime_parse_storage')) {
    function admin_datetime_parse_storage(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || str_starts_with($raw, '0000-00-00')) {
            return null;
        }

        try {
            $from = new \DateTimeZone(admin_datetime_storage_timezone());

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1) {
                return new \DateTimeImmutable($raw . ' 00:00:00', $from);
            }

            $normalized = str_replace('T', ' ', $raw);
            if (preg_match('/[Zz]|[+-]\d{2}:?\d{2}$/', $normalized) === 1) {
                return new \DateTimeImmutable($normalized);
            }

            return new \DateTimeImmutable($normalized, $from);
        } catch (\Throwable) {
            return null;
        }
    }
}

if (! function_exists('admin_datetime_to_iso_utc')) {
    function admin_datetime_to_iso_utc(mixed $value): ?string
    {
        $dt = admin_datetime_parse_storage($value);
        if ($dt === null) {
            return null;
        }

        return $dt->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
}

if (! function_exists('admin_format_datetime')) {
    /**
     * Date/heure pour l’admin : <time> converti en fuseau du navigateur (JJ-MM-AAAA hh:mm:ss).
     * Ne pas passer dans esc() — HTML sûr généré ici.
     */
    function admin_format_datetime(mixed $value): string
    {
        $iso = admin_datetime_to_iso_utc($value);
        if ($iso === null) {
            return '';
        }

        $dt = admin_datetime_parse_storage($value);
        $fallback = $dt !== null
            ? $dt->setTimezone(new \DateTimeZone('UTC'))->format('d-m-Y H:i:s')
            : '';

        return '<time class="js-admin-datetime text-nowrap" datetime="' . esc($iso, 'attr') . '">'
            . esc($fallback) . '</time>';
    }
}

if (! function_exists('admin_format_datetime_plain')) {
    /** Texte seul (export CSV, etc.) dans le fuseau cookie du client. */
    function admin_format_datetime_plain(mixed $value): string
    {
        $dt = admin_datetime_parse_storage($value);
        if ($dt === null) {
            return '';
        }

        try {
            return $dt->setTimezone(new \DateTimeZone(admin_client_timezone()))->format('d-m-Y H:i:s');
        } catch (\Throwable) {
            return $dt->format('d-m-Y H:i:s');
        }
    }
}

if (! function_exists('admin_datetime_local_to_storage')) {
    /**
     * datetime-local du navigateur → DATETIME BDD (fuseau cookie client → stockage UTC).
     */
    function admin_datetime_local_to_storage(?string $localInput): ?string
    {
        $raw = trim((string) $localInput);
        if ($raw === '') {
            return null;
        }

        try {
            $local = new \DateTimeZone(admin_client_timezone());
            $store = new \DateTimeZone(admin_datetime_storage_timezone());
            $normalized = str_replace(' ', 'T', $raw);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized) === 1) {
                $normalized .= 'T00:00:00';
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $normalized) === 1) {
                $normalized .= ':00';
            }

            $dt = new \DateTimeImmutable($normalized, $local);

            return $dt->setTimezone($store)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}

if (! function_exists('admin_datetime_input_utc_attr')) {
    /** Attribut data-admin-datetime-utc pour remplir un datetime-local côté navigateur. */
    function admin_datetime_input_utc_attr(mixed $value): string
    {
        $iso = admin_datetime_to_iso_utc($value);
        if ($iso === null) {
            return '';
        }

        return ' data-admin-datetime-utc="' . esc($iso, 'attr') . '"';
    }
}

if (! function_exists('admin_pp_is_junk_repeat_line')) {
    /** Valeurs parasites (ancien bug formulaire : libellé du bouton enregistré comme ligne). */
    function admin_pp_is_junk_repeat_line(string $line): bool
    {
        $t = mb_strtolower(trim($line));

        return $t === '' || $t === 'retirer' || $t === 'retire' || $t === '×' || $t === 'x';
    }
}

if (! function_exists('admin_pp_scrub_junk_text')) {
    /** Champ texte : vide si valeur parasite (ex. libellé bouton enregistré par erreur). */
    function admin_pp_scrub_junk_text(string $line): string
    {
        $s = trim($line);

        return admin_pp_is_junk_repeat_line($s) ? '' : $s;
    }
}

if (! function_exists('admin_pp_repeat_scalar_lines')) {
    /**
     * Lignes texte pour formulaire blocs : valeurs remplies + une ligne vide finale.
     *
     * @param list<mixed> $raw
     * @return list<string>
     */
    function admin_pp_repeat_scalar_lines(array $raw): array
    {
        $lines = [];
        foreach (array_values($raw) as $item) {
            $s = trim(is_string($item) ? $item : (string) $item);
            if ($s !== '' && ! admin_pp_is_junk_repeat_line($s)) {
                $lines[] = $s;
            }
        }
        $lines[] = '';

        return $lines;
    }
}

if (! function_exists('admin_pp_repeat_object_rows')) {
    /**
     * Lignes objet pour formulaire blocs : lignes non vides + un modèle vide final.
     *
     * @param list<mixed> $raw
     * @param callable(array<string, mixed>): bool $isEmpty
     * @param array<string, mixed> $emptyTemplate
     * @return list<array<string, mixed>>
     */
    function admin_pp_repeat_object_rows(array $raw, callable $isEmpty, array $emptyTemplate): array
    {
        $rows = [];
        foreach (array_values($raw) as $row) {
            if (! is_array($row) || $isEmpty($row)) {
                continue;
            }
            $rows[] = $row;
        }
        $rows[] = $emptyTemplate;

        return $rows;
    }
}
