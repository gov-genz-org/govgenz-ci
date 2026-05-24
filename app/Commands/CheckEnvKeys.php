<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Compare env.example (déployé avec le code) au .env du serveur.
 */
class CheckEnvKeys extends BaseCommand
{
    protected $group       = 'govgenz';
    protected $name        = 'env:check';
    protected $usage       = 'env:check [--template-only] [--strict]';
    protected $description = 'Liste les clés présentes dans env.example mais absentes du .env local (à lancer sur staging/prod après un deploy)';
    protected $options     = [
        '--template-only' => 'Valide uniquement env.example (CI / local sans .env serveur)',
        '--strict'        => 'Signale aussi les valeurs encore au placeholder (CHANGER_, REMPLACER_)',
    ];

    public function run(array $params)
    {
        $root     = rtrim(ROOTPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $example  = $root . 'env.example';
        $envFile  = $root . '.env';
        $template = CLI::getOption('template-only') !== null;
        $strict   = CLI::getOption('strict') !== null;

        if (! is_file($example)) {
            CLI::error('Fichier introuvable : ' . $example);
        } elseif ($this->parseEnvKeys($example) === []) {
            CLI::error('Aucune clé active dans env.example.');
        } elseif ($template) {
            $expected = $this->parseEnvKeys($example);
            CLI::write('env.example : ' . count($expected) . ' clé(s) documentée(s).', 'green');
        } elseif (! is_file($envFile)) {
            CLI::error('Fichier .env introuvable : ' . $envFile);
            CLI::write('Créez-le à partir de env.example (valeurs propres à ce serveur).', 'yellow');
        } else {
            $this->compareEnvFiles($example, $envFile, $strict);
        }
    }

    private function compareEnvFiles(string $example, string $envFile, bool $strict): void
    {
        $expected     = $this->parseEnvKeys($example);
        $actual       = $this->parseEnvKeys($envFile);
        $missing      = array_values(array_diff($expected, $actual));
        $placeholders = $strict ? $this->findPlaceholderValues($envFile, $expected) : [];

        if ($missing !== []) {
            CLI::write('Clés manquantes dans .env (ajoutez-les à la main sur ce serveur) :', 'red');
            foreach ($missing as $key) {
                CLI::write('  - ' . $key);
            }
        }

        if ($placeholders !== []) {
            CLI::write('Valeurs encore au placeholder dans .env :', 'yellow');
            foreach ($placeholders as $key) {
                CLI::write('  - ' . $key);
            }
        }

        $extra = array_values(array_diff($actual, $expected));
        if ($extra !== []) {
            CLI::write('Clés dans .env mais pas dans env.example (OK, spécifiques au serveur) :', 'cyan');
            foreach ($extra as $key) {
                CLI::write('  - ' . $key);
            }
        }

        $hasProblems = $missing !== [] || $placeholders !== [];

        if ($hasProblems) {
            CLI::newLine();
            CLI::write('Corrigez le .env sur CE serveur (staging ou prod), puis relancez env:check.', 'yellow');
        } else {
            CLI::write('.env contient toutes les clés requises par env.example.', 'green');
        }
    }

    /**
     * @return list<string>
     */
    private function parseEnvKeys(string $path): array
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return [];
        }

        $keys = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^([A-Za-z0-9_.-]+)\s*=/', $line, $m) !== 1) {
                continue;
            }

            $keys[] = $m[1];
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param list<string> $keys
     *
     * @return list<string>
     */
    private function findPlaceholderValues(string $path, array $keys): array
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return [];
        }

        $found = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^([A-Za-z0-9_.-]+)\s*=\s*(.*)$/', $line, $m) !== 1) {
                continue;
            }

            $key = $m[1];
            if (! in_array($key, $keys, true)) {
                continue;
            }

            $value = trim($m[2], " \t'\"");
            if (
                str_contains($value, 'CHANGER_')
                || str_contains($value, 'REMPLACER_')
                || str_contains($value, 'example.org')
            ) {
                $found[] = $key;
            }
        }

        return $found;
    }
}
