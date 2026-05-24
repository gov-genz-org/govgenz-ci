<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\CmsMediaStorage;
use App\Models\CmsMediaModel;
use App\Models\CmsPageModel;
use App\Models\CmsPostModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MediaOrphans extends BaseCommand
{
    protected $group       = 'govgenz';
    protected $name        = 'media:orphans';
    protected $description = 'Liste les fichiers médiathèque non référencés dans les contenus CMS (pages / articles)';
    protected $options     = [
        '--delete' => 'Supprimer les lignes cms_media et fichiers (destructif)',
    ];

    public function run(array $params): void
    {
        $mediaRows = model(CmsMediaModel::class)->findAll();
        $referenced  = [];

        foreach (model(CmsPageModel::class)->findAll() as $row) {
            $this->collectRefs((string) ($row['body_html'] ?? ''), $referenced);
        }
        foreach (model(CmsPostModel::class)->findAll() as $row) {
            $this->collectRefs((string) ($row['body_html'] ?? ''), $referenced);
            $this->collectRefs((string) ($row['excerpt'] ?? ''), $referenced);
        }

        $orphans = [];
        foreach ($mediaRows as $m) {
            $fn = (string) ($m['stored_filename'] ?? '');
            if ($fn === '') {
                continue;
            }
            if (! isset($referenced[$fn])) {
                $orphans[] = $m;
            }
        }

        if ($orphans === []) {
            CLI::write('Aucun média orphelin détecté.', 'green');

            return;
        }

        CLI::write(count($orphans) . ' fichier(s) potentiellement orphelin(s) :', 'yellow');
        foreach ($orphans as $o) {
            CLI::write('  #' . ($o['id'] ?? '?') . ' ' . ($o['stored_filename'] ?? '') . ' — ' . ($o['original_name'] ?? ''));
        }

        if (CLI::getOption('delete')) {
            $model = model(CmsMediaModel::class);
            foreach ($orphans as $o) {
                $id  = (int) ($o['id'] ?? 0);
                $fn  = (string) ($o['stored_filename'] ?? '');
                $path = CmsMediaStorage::filePath($fn);
                if (is_file($path)) {
                    @unlink($path);
                }
                if ($id > 0) {
                    $model->delete($id);
                }
            }
            CLI::write('Suppression effectuée.', 'green');
        } else {
            CLI::write('Relancez avec --delete pour supprimer (base + fichier sur disque).', 'white');
        }
    }

    /**
     * @param array<string, true> $referenced
     */
    private function collectRefs(string $html, array &$referenced): void
    {
        if ($html === '') {
            return;
        }
        if (! preg_match_all('#(?:uploads/cms/|/uploads/cms/)([^"\'\s<>]+)#i', $html, $m)) {
            return;
        }
        foreach ($m[1] as $fn) {
            $fn = rawurldecode((string) $fn);
            $fn = basename($fn);
            if ($fn !== '') {
                $referenced[$fn] = true;
            }
        }
    }
}
