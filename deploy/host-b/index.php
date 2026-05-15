<?php

/**
 * Point d’entrée pour le vhost « projects.* » (même appli CodeIgniter, pas de copie de app/vendor).
 *
 * Déplacer ce fichier (et .htaccess) vers le dossier que le sous-domaine utilise comme racine web.
 *
 * Choisir UN seul mode pour $projectARoot :
 *
 * — Cas A : le docroot du sous-domaine est un sous-dossier `projects/` DANS la racine du projet
 *   (au même niveau que `app/`, `public/`, `vendor/`). Ex. FTP : …/racine-ci/projects/index.php
 *
 * — Cas B : le docroot est un autre dossier sur le serveur ; utiliser le chemin ABSOLU vers la racine CI.
 *
 * Statiques : depuis ce docroot, `/assets/…` cherche dans `projects/assets/`. Créer un lien symbolique
 *   `projects/assets` → `../public/assets` (ou équivalent) si les CSS/JS ne se chargent pas.
 *
 * .env (racine du projet) : app.projectsHost + app.projectsBaseURL (voir .env).
 */

declare(strict_types=1);

use CodeIgniter\Boot;
use Config\Paths;

// --- Cas A (docroot = …/racine-du-projet/projects/) — décommenter si c’est ton cas.
$projectARoot = dirname(__DIR__) . DIRECTORY_SEPARATOR;

// --- Cas B (autre dossier FTP) — commenter le cas A, décommenter et adapter le cas B.
// $projectARoot = '/CHEMIN/ABSOLU/VERS/RACINE-DU-PROJET-CI/';
// $projectARoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $projectARoot), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

if (! is_file($projectARoot . 'app/Config/Paths.php')) {
    header('HTTP/1.1 500 Internal Server Error', true, 500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Configuration : racine projet CI invalide (app/Config/Paths.php introuvable). Vérifie le cas A/B dans index.php.';
    exit(1);
}

$minPhpVersion = '8.2';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    header('HTTP/1.1 503 Service Unavailable', true, 503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'PHP ' . $minPhpVersion . '+ requis.';
    exit(1);
}

define('FCPATH', $projectARoot);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

require FCPATH . 'app/Config/Paths.php';

$paths = new Paths();

require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
