<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Chemins disque + URL publique de la médiathèque CMS.
 *
 * Prod (docroot = racine projet) : fichiers dans {racine}/uploads/cms/ (hors public/).
 * Docker / docroot = public/ : fichiers dans public/uploads/cms/.
 * Option .env app.mediaStoragePath : chemin absolu ou relatif à la racine projet.
 */
final class CmsMediaStorage
{
    public static function storageDir(): string
    {
        $custom = trim((string) env('app.mediaStoragePath', ''));
        if ($custom !== '') {
            return self::normalizeDir(self::resolvePath($custom));
        }

        return self::normalizeDir(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms');
    }

    public static function publicUrl(string $storedFilename): string
    {
        $fn = self::safeFilename($storedFilename);
        if ($fn === '') {
            return '';
        }

        return base_url('uploads/cms/' . $fn);
    }

    public static function filePath(string $storedFilename): string
    {
        $fn = self::safeFilename($storedFilename);
        if ($fn === '') {
            return '';
        }

        return self::storageDir() . $fn;
    }

    public static function ensureStorageDir(): bool
    {
        $dir = self::storageDir();

        return is_dir($dir) || mkdir($dir, 0755, true);
    }

    private static function projectRoot(): string
    {
        return rtrim(realpath(ROOTPATH) ?: ROOTPATH, '/\\');
    }

    private static function resolvePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path));
        if ($path === '') {
            return self::storageDir();
        }

        if (self::isAbsolute($path)) {
            return $path;
        }

        return self::projectRoot() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    private static function isAbsolute(string $path): bool
    {
        if ($path !== '' && $path[0] === DIRECTORY_SEPARATOR) {
            return true;
        }

        return (bool) preg_match('#^[A-Za-z]:\\\\#', $path);
    }

    private static function normalizeDir(string $path): string
    {
        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    private static function safeFilename(string $storedFilename): string
    {
        $fn = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, trim($storedFilename)));

        return $fn !== '' && $fn !== '.' && $fn !== '..' ? $fn : '';
    }
}
