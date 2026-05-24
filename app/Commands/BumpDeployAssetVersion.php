<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BumpDeployAssetVersion extends BaseCommand
{
    protected $group       = 'govgenz';
    protected $name        = 'deploy:asset-version';
    protected $usage       = 'deploy:asset-version [version]';
    protected $description = 'Met à jour writable/deploy_version.txt (équivalent : deploy/bump-asset-version.sh, sans PHP sur le serveur)';
    protected $arguments   = [
        'version' => 'Identifiant optionnel (défaut : horodatage UTC YmdHis)',
    ];

    public function run(array $params): void
    {
        $version = trim((string) ($params[0] ?? ''));
        if ($version === '') {
            $version = gmdate('YmdHis');
        }

        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $version)) {
            CLI::error('Version invalide (caractères autorisés : lettres, chiffres, . _ -).');

            return;
        }

        $path = WRITEPATH . 'deploy_version.txt';
        if (@file_put_contents($path, $version . "\n") === false) {
            CLI::error('Impossible d’écrire : ' . $path);

            return;
        }

        CLI::write('Version assets front : ' . $version, 'green');
        CLI::write('Fichier : ' . $path);
    }
}
