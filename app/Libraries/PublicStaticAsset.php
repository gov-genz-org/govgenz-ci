<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Assets versionnés sous public/assets/… (icônes secteurs, structure, etc.).
 *
 * En prod mutualisée, FCPATH peut être la racine du projet (index.php racine) alors que
 * les fichiers sont dans public/ — les URLs /assets/… fonctionnent via .htaccess,
 * mais is_file(FCPATH . 'assets/…') échouait sans le préfixe public/.
 */
final class PublicStaticAsset
{
    public static function urlIfExists(string $relative): ?string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        if ($relative === '' || str_contains($relative, '..')) {
            return null;
        }

        foreach (self::candidatePaths($relative) as $path) {
            if (is_file($path)) {
                helper('url');

                return base_url($relative);
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function candidatePaths(string $relative): array
    {
        $file = str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $paths = [FCPATH . $file];

        $publicDir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
        $paths[] = $publicDir . $file;

        return array_values(array_unique($paths));
    }
}
