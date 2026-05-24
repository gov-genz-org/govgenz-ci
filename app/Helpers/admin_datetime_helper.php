<?php

declare(strict_types=1);

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
