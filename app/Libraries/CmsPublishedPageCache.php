<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Cache des pages CMS publiées (slug + locale), utilisé par le front office.
 * Invalidé à chaque écriture sur cms_pages.
 */
final class CmsPublishedPageCache
{
    public const TTL_SECONDS = 7200;

    private const PREFIX = 'cms_pub_';

    private const MISS = '__miss__';

    /**
     * @param callable(): ?array<string, mixed> $resolver
     * @return ?array<string, mixed>
     */
    public static function remember(string $locale, string $slug, callable $resolver): ?array
    {
        $key = self::key($locale, $slug);

        $cached = cache()->get($key);
        if ($cached === self::MISS) {
            return null;
        }
        if (is_array($cached)) {
            return $cached;
        }

        $row = $resolver();
        if ($row === null) {
            cache()->save($key, self::MISS, 300);

            return null;
        }

        cache()->save($key, $row, self::TTL_SECONDS);

        return $row;
    }

    public static function forget(string $locale, string $slug): void
    {
        cache()->delete(self::key($locale, $slug));
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function forgetRow(array $row): void
    {
        $locale = trim((string) ($row['locale'] ?? ''));
        $slug   = trim((string) ($row['slug'] ?? ''));
        if ($locale === '' || $slug === '') {
            return;
        }

        self::forget($locale, $slug);
    }

    public static function flushAll(): void
    {
        try {
            cache()->deleteMatching(self::PREFIX . '*');
        } catch (\Throwable $e) {
            log_message('warning', 'CmsPublishedPageCache::flushAll : {msg}', ['msg' => $e->getMessage()]);
        }
    }

    private static function key(string $locale, string $slug): string
    {
        $locale = strtolower(trim($locale));
        $slug   = strtolower(trim($slug));

        return self::PREFIX . $locale . '_' . md5($slug);
    }
}
