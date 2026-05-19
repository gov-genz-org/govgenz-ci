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
