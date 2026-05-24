<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Cache fichier (CI4) des listes géographiques servies par GeoCatalog (BO).
 * Invalidé à chaque mdg:import-geo.
 */
final class GeoCatalogCache
{
    public const TTL_SECONDS = 86400;

    private const PREFIX = 'mdg_geo_';

    /**
     * @template T
     * @param callable(): T $producer
     * @return T
     */
    public static function remember(string $suffix, callable $producer): mixed
    {
        $key = self::PREFIX . $suffix;

        $cached = cache()->get($key);
        if (is_array($cached) && array_key_exists('v', $cached)) {
            return $cached['v'];
        }

        $value = $producer();
        cache()->save($key, ['v' => $value], self::TTL_SECONDS);

        return $value;
    }

    /**
     * @param list<int> $ids
     */
    public static function idsSuffix(string $level, array $ids): string
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        sort($ids);

        return $level . '_' . md5(implode(',', $ids));
    }

    public static function flushAll(): void
    {
        try {
            cache()->deleteMatching(self::PREFIX . '*');
        } catch (\Throwable $e) {
            log_message('warning', 'GeoCatalogCache::flushAll : {msg}', ['msg' => $e->getMessage()]);
        }
    }
}
