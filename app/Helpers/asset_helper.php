<?php

declare(strict_types=1);

if (! function_exists('front_assets_max_mtime')) {
    /**
     * Dernière modification des CSS/JS front (recalculé à chaque déploiement rsync/FTP).
     */
    function front_assets_max_mtime(): int
    {
        $maxMtime = 0;
        $globs    = [
            FCPATH . 'assets/css/*.css',
            FCPATH . 'js/front/*.js',
        ];

        foreach ($globs as $pattern) {
            $paths = glob($pattern) ?: [];
            foreach ($paths as $path) {
                if (is_file($path)) {
                    $maxMtime = max($maxMtime, (int) filemtime($path));
                }
            }
        }

        return $maxMtime;
    }
}

if (! function_exists('front_asset_version')) {
    /**
     * Version du cache front (?v= sur les CSS/JS).
     *
     * Sans PHP CLI sur le serveur : par défaut = max(mtime) des fichiers sous public/assets/css/
     * et public/js/front/ (mis à jour automatiquement à chaque déploiement des assets).
     *
     * Overrides optionnels :
     * - app.assetVersion dans .env (éditable via FTP/panneau hébergeur)
     * - writable/deploy_version.txt (une ligne, ex. echo abc123 > deploy_version.txt avant rsync)
     */
    function front_asset_version(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $fromEnv = trim((string) env('app.assetVersion', ''));
        if ($fromEnv !== '') {
            $cached = $fromEnv;

            return $cached;
        }

        $versionFile = WRITEPATH . 'deploy_version.txt';
        if (is_readable($versionFile)) {
            $fromFile = trim((string) file_get_contents($versionFile));
            if ($fromFile !== '') {
                $cached = $fromFile;

                return $cached;
            }
        }

        $maxMtime = front_assets_max_mtime();
        $cached   = $maxMtime > 0 ? (string) $maxMtime : '1';

        return $cached;
    }
}

if (! function_exists('public_asset_url')) {
    /**
     * URL publique d’un fichier statique avec paramètre de version (CSS/JS/MJS).
     */
    function public_asset_url(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $url          = base_url($relativePath);

        if (! preg_match('/\.(css|js|mjs)(\?.*)?$/i', $relativePath)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . rawurlencode(front_asset_version());
    }
}
