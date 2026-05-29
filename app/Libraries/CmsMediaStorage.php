<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Chemins disque + URL publique de la médiathèque CMS.
 *
 * Prod (docroot = racine projet) : écriture dans {racine}/uploads/cms/ (hors public/).
 * Docker / docroot = public/ : écriture dans public/uploads/cms/.
 * Lecture : tous les emplacements connus (racine, public/, chemin .env).
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

        if (self::docrootIsPublicFolder()) {
            return self::normalizeDir(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms');
        }

        return self::normalizeDir(self::resolvePath('uploads/cms'));
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

    /**
     * Vérifie la présence du fichier sur disque (tous emplacements connus).
     */
    public static function fileExists(string $storedFilename): bool
    {
        foreach (self::readableFileCandidates($storedFilename) as $path) {
            if (is_file($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Chemin disque du fichier s’il existe, sinon chemin d’écriture par défaut.
     */
    public static function resolveReadablePath(string $storedFilename): string
    {
        foreach (self::readableFileCandidates($storedFilename) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return self::filePath($storedFilename);
    }

    /**
     * @return list<string>
     */
    private static function readableFileCandidates(string $storedFilename): array
    {
        $fn = self::safeFilename($storedFilename);
        if ($fn === '') {
            return [];
        }

        $paths = [];
        foreach (self::uploadBaseDirs() as $dir) {
            $paths[] = $dir . $fn;
        }

        return array_values(array_unique($paths));
    }

    /**
     * Dossiers où les fichiers CMS peuvent exister (historique Docker vs prod mutualisée).
     *
     * @return list<string>
     */
    private static function uploadBaseDirs(): array
    {
        $root = self::projectRoot();
        $dirs = [
            self::storageDir(),
            self::normalizeDir($root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms'),
            self::normalizeDir($root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms'),
            self::normalizeDir(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms'),
        ];

        $custom = trim((string) env('app.mediaStoragePath', ''));
        if ($custom !== '') {
            $dirs[] = self::normalizeDir(self::resolvePath($custom));
        }

        $unique = [];
        foreach ($dirs as $dir) {
            if ($dir !== '' && ! in_array($dir, $unique, true)) {
                $unique[] = $dir;
            }
        }

        return $unique;
    }

    private static function docrootIsPublicFolder(): bool
    {
        $publicIndex = self::projectRoot() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
        if (! is_file($publicIndex)) {
            return false;
        }

        $fcPath  = realpath(rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR) ?: rtrim(FCPATH, '/\\');
        $pubPath = realpath(dirname($publicIndex)) ?: dirname($publicIndex);

        return $fcPath === $pubPath;
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
        $storedFilename = trim($storedFilename);
        if ($storedFilename === '' || str_contains($storedFilename, '..')) {
            return '';
        }

        $fn = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $storedFilename));
        if ($fn === '' || $fn === '.' || $fn === '..' || str_starts_with($fn, '.')) {
            return '';
        }

        return $fn;
    }
}
