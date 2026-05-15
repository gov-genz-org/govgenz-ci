<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\MdgGeographyImporter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportMdgGeography extends BaseCommand
{
    protected $group       = 'mdg';
    protected $name        = 'mdg:import-geo';
    protected $description = 'Importe régions, districts, communes (SQL) et fokontany (CSV) depuis docs/.';
    protected $usage       = 'mdg:import-geo';

    public function run(array $params): void
    {
        [$sql, $csv] = $this->resolveDataFiles();

        CLI::write('SQL : ' . $sql, 'cyan');
        if ($csv !== null) {
            CLI::write('CSV fokontany : ' . $csv, 'cyan');
        } else {
            CLI::write('CSV fokontany : absent (import communes uniquement)', 'yellow');
        }

        $importer = new MdgGeographyImporter(db_connect());
        $importer->import($sql, $csv);

        CLI::write('Import géographie Madagascar terminé.', 'green');
        CLI::write('Provinces : ' . (string) db_connect()->table('mdg_provinces')->countAllResults());
        CLI::write('Régions : ' . (string) db_connect()->table('mdg_regions')->countAllResults());
        CLI::write('Districts : ' . (string) db_connect()->table('mdg_districts')->countAllResults());
        CLI::write('Communes : ' . (string) db_connect()->table('mdg_communes')->countAllResults());
        CLI::write('Fokontany : ' . (string) db_connect()->table('mdg_fokontany')->countAllResults());
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function resolveDataFiles(): array
    {
        $sqlName = 'regions-communes-district.sql';
        $csvName = 'mada_fokontany_payload.csv';

        $dirs = [
            dirname(ROOTPATH) . DIRECTORY_SEPARATOR . 'docs',
            '/var/www/docs',
            ROOTPATH . 'database' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'mdg-geo',
        ];

        foreach ($dirs as $dir) {
            $sql = $dir . DIRECTORY_SEPARATOR . $sqlName;
            if (! is_readable($sql)) {
                continue;
            }
            $csv = $dir . DIRECTORY_SEPARATOR . $csvName;

            return [$sql, is_readable($csv) ? $csv : null];
        }

        throw new \RuntimeException(
            'Fichier SQL introuvable. Attendu : docs/' . $sqlName . ' à la racine du dépôt (tanijanaka/docs/). '
            . 'Avec Docker : montez ../docs sur /var/www/docs dans govgenz-local/docker-compose.yml, '
            . 'puis redémarrez : docker compose up -d'
        );
    }
}
