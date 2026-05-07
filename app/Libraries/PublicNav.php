<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Résolution URL et état actif pour les liens du menu public (site_nav_items).
 */
final class PublicNav
{
    /**
     * @param array<string, mixed> $row
     */
    public static function hrefFromRow(array $row): string
    {
        helper(['url', 'locale']);

        $kind   = strtolower(trim((string) ($row['href_kind'] ?? '')));
        $target = trim((string) ($row['href_target'] ?? ''));

        return match ($kind) {
            'home' => localized_site_url(''),
            'segment' => $target !== '' ? localized_site_url($target) : localized_site_url(''),
            'path' => self::hrefPathTarget($target),
            'external' => $target !== '' ? $target : localized_site_url(''),
            default => localized_site_url(''),
        };
    }

    private static function hrefPathTarget(string $target): string
    {
        if ($target === '') {
            return localized_site_url('');
        }

        if (str_starts_with($target, 'admin')) {
            return site_url($target);
        }

        return localized_site_url($target);
    }

    public static function isActive(string $matchKey, string $navActive, string $seg1, string $seg2): bool
    {
        $mk = trim($matchKey);
        if ($mk === '' || $mk === 'none') {
            return false;
        }

        return match ($mk) {
            'home' => $navActive === 'home',
            'press' => $navActive === 'press',
            'join' => $navActive === 'join',
            'contact' => $navActive === 'contact',
            'admin_login' => $seg1 === 'admin' && $seg2 === 'login',
            default => $navActive === '' && $seg1 === $mk,
        };
    }
}
