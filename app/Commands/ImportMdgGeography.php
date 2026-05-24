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
    protected $description = 'Importe la géographie MDG : si liste_fokontany_par_district.json est présent, régions/districts/communes/fokontany viennent du JSON (SQL = provinces + aide province_id). Sinon référentiel SQL + CSV fokontany.';
    protected $usage       = 'mdg:import-geo';

    public function run(array $params): void
    {
        [$sql, $csv, $json] = $this->resolveDataFiles();

        CLI::write('SQL : ' . $sql, 'cyan');
        if ($json !== null) {
            CLI::write('Référence JSON (régions, districts, communes, fokontany) : ' . $json, 'cyan');
        } elseif ($csv !== null) {
            CLI::write('Fokontany (CSV) : ' . $csv, 'cyan');
        } else {
            CLI::write('Fokontany : aucun fichier JSON/CSV — import sans fokontany.', 'yellow');
        }

        $importer = new MdgGeographyImporter(db_connect());
        $importer->import($sql, $csv, $json);

        CLI::write('Import géographie Madagascar terminé (cache géo BO vidé).', 'green');
        CLI::write('Provinces : ' . (string) db_connect()->table('mdg_provinces')->countAllResults());
        CLI::write('Régions : ' . (string) db_connect()->table('mdg_regions')->countAllResults());
        CLI::write('Districts : ' . (string) db_connect()->table('mdg_districts')->countAllResults());
        CLI::write('Communes : ' . (string) db_connect()->table('mdg_communes')->countAllResults());
        CLI::write('Fokontany : ' . (string) db_connect()->table('mdg_fokontany')->countAllResults());
    }

    /**
     * @return array{0: string, 1: string|null, 2: string|null} sql, csv fallback, json (prioritaire à l’import)
     */
    private function resolveDataFiles(): array
    {
        $sqlName  = 'regions-communes-district.sql';
        $csvName  = 'mada_fokontany_payload.csv';
        $jsonName = 'liste_fokontany_par_district.json';

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
            $json = $dir . DIRECTORY_SEPARATOR . $jsonName;
            $csv  = $dir . DIRECTORY_SEPARATOR . $csvName;

            return [
                $sql,
                is_readable($csv) ? $csv : null,
                is_readable($json) ? $json : null,
            ];
        }

        throw new \RuntimeException(
            'Fichier SQL introuvable. Attendu : docs/' . $sqlName . ' à la racine du dépôt (tanijanaka/docs/). '
            . 'Avec Docker : montez ../docs sur /var/www/docs dans govgenz-local/docker-compose.yml, '
            . 'puis redémarrez : docker compose up -d'
        );
    }
}
