<?php

declare(strict_types=1);

if (! function_exists('email_absolute_url')) {
    /**
     * URL absolue pour liens / images dans les e-mails (baseURL .env).
     */
    function email_absolute_url(string $relativePath = ''): string
    {
        $base = rtrim((string) config('App')->baseURL, '/ ');
        $relativePath = ltrim($relativePath, '/');
        if ($relativePath === '') {
            return $base . '/';
        }

        return $base . '/' . $relativePath;
    }
}

if (! function_exists('email_moderator_notification_addresses_from_env')) {
    /**
     * Secours si aucun compte staff actif en base (déploiement, migration).
     *
     * @return list<string>
     */
    function email_moderator_notification_addresses_from_env(): array
    {
        $raw = trim((string) env('join.notification.to', ''));
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', $raw) ?: [];
        $out   = [];
        foreach ($parts as $part) {
            $addr = mb_strtolower(trim($part));
            if ($addr !== '' && filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                $out[] = $addr;
            }
        }

        return array_values(array_unique($out));
    }
}

if (! function_exists('email_moderator_notification_addresses')) {
    /**
     * Destinataires modérateurs : comptes staff actifs (table staff_users), sinon join.notification.to.
     *
     * @return list<string>
     */
    function email_moderator_notification_addresses(): array
    {
        try {
            $fromDb = model(\App\Models\StaffUserModel::class)->notificationEmailAddresses();
            if ($fromDb !== []) {
                return $fromDb;
            }
        } catch (\Throwable $e) {
            log_message('error', 'email_moderator_notification_addresses: {msg}', ['msg' => $e->getMessage()]);
        }

        return email_moderator_notification_addresses_from_env();
    }
}

if (! function_exists('email_brand_logo_url')) {
    /**
     * Logo pour les e-mails (PNG de préférence ; override via email.logoUrl).
     */
    function email_brand_logo_url(): string
    {
        $custom = trim((string) env('email.logoUrl', ''));
        if ($custom !== '' && filter_var($custom, FILTER_VALIDATE_URL)) {
            return $custom;
        }

        $png = FCPATH . 'assets/logo-256.png';
        if (is_file($png)) {
            return email_absolute_url('assets/logo-256.png');
        }

        $qr = FCPATH . 'assets/img/govgenz-logo-qr.png';
        if (is_file($qr)) {
            return email_absolute_url('assets/img/govgenz-logo-qr.png');
        }

        return email_absolute_url('assets/img/govgenz-logo.svg');
    }
}
