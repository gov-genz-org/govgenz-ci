<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class BackupDatabase extends BaseCommand
{
    protected $group       = 'govgenz';
    protected $name        = 'db:backup';
    protected $usage       = 'db:backup [options]';
    protected $description = 'Exporte la base MySQL avec mysqldump vers writable/backups/ (variable BACKUP_PATH optionnelle)';
    protected $options     = [
        '--connection' => 'Groupe de connexion (défaut : default)',
    ];

    public function run(array $params): void
    {
        $group = CLI::getOption('connection') ?? 'default';
        /** @var array<string, mixed> $dbCfg */
        $dbCfg = config(Database::class)->{$group};
        if (($dbCfg['DBDriver'] ?? '') !== 'MySQLi') {
            CLI::error('Ce groupe ne utilise pas MySQLi ; utilisez mysqldump ou pg_dump manuellement.');

            return;
        }

        $customDir = getenv('BACKUP_PATH');
        $dir       = is_string($customDir) && $customDir !== '' ? $customDir : WRITEPATH . 'backups';
        if (! is_dir($dir) && ! @mkdir($dir, 0750, true) && ! is_dir($dir)) {
            CLI::error('Impossible de créer le dossier : ' . $dir);

            return;
        }

        $mysqldump = getenv('MYSQLDUMP_PATH');
        $binary    = is_string($mysqldump) && $mysqldump !== '' ? $mysqldump : 'mysqldump';

        $host = (string) ($dbCfg['hostname'] ?? '127.0.0.1');
        $user = (string) ($dbCfg['username'] ?? 'root');
        $pass = (string) ($dbCfg['password'] ?? '');
        $name = (string) ($dbCfg['database'] ?? '');
        $port = (int) ($dbCfg['port'] ?? 3306);

        if ($name === '') {
            CLI::error('Nom de base vide dans la configuration.');

            return;
        }

        $stamp = date('Y-m-d_His');
        $file  = $dir . DIRECTORY_SEPARATOR . $name . '_' . $stamp . '.sql';

        $parts = [
            escapeshellcmd($binary),
            '--single-transaction',
            '--quick',
            '--default-character-set=utf8mb4',
            '-h' . escapeshellarg($host),
            '-P' . $port,
            '-u' . escapeshellarg($user),
            escapeshellarg($name),
        ];
        $cmdInner = implode(' ', $parts) . ' > ' . escapeshellarg($file);

        $shell = PHP_OS_FAMILY === 'Windows' ? 'cmd.exe' : '/bin/sh';
        $arg0  = PHP_OS_FAMILY === 'Windows' ? '/c' : '-c';
        $env   = $_ENV;
        if ($pass !== '') {
            $env['MYSQL_PWD'] = $pass;
        }

        $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc             = proc_open([$shell, $arg0, $cmdInner], $descriptorSpec, $pipes, null, $env);
        if (! is_resource($proc)) {
            CLI::error('Impossible de lancer mysqldump (proc_open).');

            return;
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $code = proc_close($proc);

        if ($code !== 0 || ! is_file($file) || filesize($file) === 0) {
            CLI::error('Échec mysqldump (code ' . $code . ').' . ($stderr !== '' ? PHP_EOL . trim($stderr) : ''));

            return;
        }

        CLI::write('Sauvegarde créée : ' . $file, 'green');
    }
}
