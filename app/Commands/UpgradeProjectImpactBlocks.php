<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\ProjectBodyHtmlToBlocks;
use App\Models\ProjectProjectModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class UpgradeProjectImpactBlocks extends BaseCommand
{
    protected $group       = 'projects';
    protected $name        = 'projects:upgrade-impact-blocks';
    protected $description = 'Convertit les blocs html (impact-tracker) en blocs impact_tracker structurés.';
    protected $usage       = 'projects:upgrade-impact-blocks [--dry-run] [--slug=slug]';

    public function run(array $params): void
    {
        $dryRun   = CLI::getOption('dry-run') !== null;
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

        $upgraded = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            $id   = (int) ($row['id'] ?? 0);
            $slug = (string) ($row['slug'] ?? '');
            $loc  = (string) ($row['locale'] ?? '');
            $raw  = trim((string) ($row['body_blocks'] ?? ''));

            if ($raw === '' || $raw === '[]') {
                $skipped++;

                continue;
            }

            $blocks = json_decode($raw, true);
            if (! is_array($blocks)) {
                CLI::write("  [skip] #{$id} {$slug} ({$loc}) — JSON invalide", 'yellow');
                $skipped++;

                continue;
            }

            $hasHtmlImpact = false;
            foreach ($blocks as $b) {
                if (is_array($b) && ($b['type'] ?? '') === 'html' && str_contains((string) ($b['html'] ?? ''), 'impact-tracker')) {
                    $hasHtmlImpact = true;

                    break;
                }
            }

            if (! $hasHtmlImpact) {
                $skipped++;

                continue;
            }

            $upgradedBlocks = ProjectBodyHtmlToBlocks::upgradeBlocksList($blocks);
            $changed = json_encode($upgradedBlocks) !== json_encode($blocks);

            if (! $changed) {
                CLI::write("  [skip] #{$id} {$slug} ({$loc}) — conversion impossible", 'yellow');
                $skipped++;

                continue;
            }

            $types = [];
            foreach ($upgradedBlocks as $b) {
                $types[] = (string) (is_array($b) ? ($b['type'] ?? '?') : '?');
            }
            $summary = count($upgradedBlocks) . ' bloc(s) : ' . implode(', ', $types);

            if ($dryRun) {
                CLI::write("  [dry] #{$id} {$slug} ({$loc}) — {$summary}", 'cyan');

                continue;
            }

            $json = json_encode($upgradedBlocks, JSON_UNESCAPED_UNICODE);
            if (! is_string($json)) {
                CLI::write("  [fail] #{$id} {$slug} ({$loc}) — encodage JSON", 'red');

                continue;
            }

            $model->update($id, ['body_blocks' => $json]);
            CLI::write("  [ok] #{$id} {$slug} ({$loc}) — {$summary}", 'green');
            $upgraded++;
        }

        CLI::newLine();
        CLI::write("Terminé — mis à jour : {$upgraded}, ignorés : {$skipped}", 'green');
        if ($dryRun) {
            CLI::write('Mode dry-run : aucune écriture en base.', 'cyan');
        }
    }
}
