<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\ProjectBodyHtmlToBlocks;
use App\Models\ProjectProjectModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigrateProjectBodyBlocks extends BaseCommand
{
    protected $group       = 'projects';
    protected $name        = 'projects:migrate-body-blocks';
    protected $description = 'Convertit les corps HTML des fiches projet (body) en body_blocks JSON.';
    protected $usage       = 'projects:migrate-body-blocks [--dry-run] [--slug=slug] [--keep-body] [--force]';

    public function run(array $params): void
    {
        $dryRun   = CLI::getOption('dry-run') !== null;
        $keepBody = CLI::getOption('keep-body') !== null;
        $force    = CLI::getOption('force') !== null;
        $slugOpt  = CLI::getOption('slug');
        $slugFilter = is_string($slugOpt) && trim($slugOpt) !== '' ? trim($slugOpt) : null;

        $model = model(ProjectProjectModel::class);
        $builder = $model->where('deleted_at', null);
        if ($slugFilter !== null) {
            $builder = $builder->where('slug', $slugFilter);
        }

        $rows = $builder->findAll();
        if ($rows === []) {
            CLI::write('Aucun projet à traiter.', 'yellow');

            return;
        }

        $migrated = 0;
        $skipped  = 0;
        $failed   = 0;

        foreach ($rows as $row) {
            $id   = (int) ($row['id'] ?? 0);
            $slug = (string) ($row['slug'] ?? '');
            $loc  = (string) ($row['locale'] ?? '');
            $mode = strtolower(trim((string) ($row['body_content_mode'] ?? 'html')));
            $body = trim((string) ($row['body'] ?? ''));
            $existingBlocks = trim((string) ($row['body_blocks'] ?? ''));

            if ($body === '') {
                CLI::write("  [skip] #{$id} {$slug} ({$loc}) — body vide", 'dark_gray');
                $skipped++;

                continue;
            }

            if ($mode === 'blocks' && $existingBlocks !== '' && $existingBlocks !== '[]' && ! $force) {
                CLI::write("  [skip] #{$id} {$slug} ({$loc}) — déjà en blocs (utilisez --force)", 'yellow');
                $skipped++;

                continue;
            }

            $blocks = ProjectBodyHtmlToBlocks::parse($body);
            if ($blocks === []) {
                CLI::write("  [fail] #{$id} {$slug} ({$loc}) — aucun bloc extrait", 'red');
                $failed++;

                continue;
            }

            $types = [];
            foreach ($blocks as $b) {
                $types[] = (string) ($b['type'] ?? '?');
            }
            $summary = count($blocks) . ' bloc(s) : ' . implode(', ', $types);

            if ($dryRun) {
                CLI::write("  [dry] #{$id} {$slug} ({$loc}) — {$summary}", 'cyan');

                continue;
            }

            $json = json_encode($blocks, JSON_UNESCAPED_UNICODE);
            if (! is_string($json)) {
                CLI::write("  [fail] #{$id} {$slug} ({$loc}) — encodage JSON", 'red');
                $failed++;

                continue;
            }

            $update = [
                'body_content_mode' => 'blocks',
                'body_blocks'       => $json,
            ];
            if (! $keepBody) {
                $update['body'] = null;
            }

            $model->update($id, $update);
            CLI::write("  [ok] #{$id} {$slug} ({$loc}) — {$summary}", 'green');
            $migrated++;
        }

        CLI::newLine();
        CLI::write("Terminé — migrés : {$migrated}, ignorés : {$skipped}, échecs : {$failed}", $failed > 0 ? 'yellow' : 'green');
        if ($dryRun) {
            CLI::write('Mode dry-run : aucune écriture en base.', 'cyan');
        }
    }
}
