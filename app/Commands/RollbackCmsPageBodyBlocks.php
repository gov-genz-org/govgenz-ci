<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\CmsBodyBlocksRenderer;
use App\Models\CmsPageModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Annule la migration pages:migrate-body-blocks : repasse en mode HTML et reconstruit body_html depuis body_blocks.
 */
class RollbackCmsPageBodyBlocks extends BaseCommand
{
    protected $group       = 'cms';
    protected $name        = 'pages:rollback-body-blocks';
    protected $description = 'Restaure content_mode=html et body_html à partir des body_blocks (annule la migration blocs).';
    protected $usage       = 'pages:rollback-body-blocks [--dry-run] [--slug=slug]';

    public function run(array $params): void
    {
        $dryRun   = CLI::getOption('dry-run') !== null;
        $slugOpt  = CLI::getOption('slug');
        $slugFilter = is_string($slugOpt) && trim($slugOpt) !== '' ? trim($slugOpt) : null;

        $model = model(CmsPageModel::class);
        $builder = $model;
        if ($slugFilter !== null) {
            $builder = $builder->where('slug', $slugFilter);
        }

        $rows = $builder->findAll();
        $restored = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            $id   = (int) ($row['id'] ?? 0);
            $slug = (string) ($row['slug'] ?? '');
            $loc  = (string) ($row['locale'] ?? '');
            $mode = strtolower(trim((string) ($row['content_mode'] ?? 'html')));

            if ($mode !== 'blocks') {
                $skipped++;

                continue;
            }

            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw === '' || $raw === '[]') {
                CLI::write("  [skip] #{$id} {$slug} ({$loc}) — blocs vides", 'yellow');
                $skipped++;

                continue;
            }

            $blocks = json_decode($raw, true);
            if (! is_array($blocks)) {
                CLI::write("  [skip] #{$id} {$slug} ({$loc}) — JSON invalide", 'red');
                $skipped++;

                continue;
            }

            $html = self::blocksToHtml($blocks);
            if ($html === '') {
                CLI::write("  [skip] #{$id} {$slug} ({$loc}) — HTML vide", 'yellow');
                $skipped++;

                continue;
            }

            if ($dryRun) {
                CLI::write("  [dry] #{$id} {$slug} ({$loc}) — " . strlen($html) . ' octets HTML', 'cyan');

                continue;
            }

            $model->update($id, [
                'content_mode' => 'html',
                'body_html'    => $html,
                'body_blocks'  => null,
            ]);
            CLI::write("  [ok] #{$id} {$slug} ({$loc}) — mode HTML restauré", 'green');
            $restored++;
        }

        CLI::newLine();
        CLI::write("Terminé — restaurés : {$restored}, ignorés : {$skipped}", 'green');
        if ($dryRun) {
            CLI::write('Mode dry-run : aucune écriture en base.', 'cyan');
        }
    }

    /**
     * @param list<mixed> $blocks
     */
    private static function blocksToHtml(array $blocks): string
    {
        $html = '';
        foreach ($blocks as $blk) {
            if (! is_array($blk)) {
                continue;
            }
            $type = (string) ($blk['type'] ?? '');
            if ($type === 'page_section') {
                $html .= trim((string) ($blk['inner_html'] ?? ''));

                continue;
            }
            if ($type === 'html') {
                $html .= trim((string) ($blk['html'] ?? ''));

                continue;
            }
            if ($type === 'metrics_section') {
                $html .= CmsBodyBlocksRenderer::render([$blk]);
            }
        }

        return trim($html);
    }
}
