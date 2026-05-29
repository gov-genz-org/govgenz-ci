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
        $mediaById  = [];
        foreach ($mediaRows as $mediaRow) {
            $id = (int) ($mediaRow['id'] ?? 0);
            $fn = (string) ($mediaRow['stored_filename'] ?? '');
            if ($id > 0 && $fn !== '') {
                $mediaById[$id] = $fn;
            }
        }

        $referenced  = [];

        foreach (model(CmsPageModel::class)->findAll() as $row) {
            $this->collectRefs((string) ($row['body_html'] ?? ''), $referenced);
            $this->collectRefs((string) ($row['body_blocks'] ?? ''), $referenced);
            $this->collectMediaId($row['hero_image_id'] ?? null, $mediaById, $referenced);
            $this->collectBlockMediaRefs((string) ($row['body_blocks'] ?? ''), $mediaById, $referenced);
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
                $path = CmsMediaStorage::resolveReadablePath($fn);
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

    /**
     * @param array<int, string> $mediaById
     * @param array<string, true> $referenced
     */
    private function collectMediaId(mixed $rawId, array $mediaById, array &$referenced): void
    {
        if ($rawId === null || $rawId === '') {
            return;
        }

        $id = (int) $rawId;
        if ($id <= 0 || ! isset($mediaById[$id])) {
            return;
        }

        $referenced[$mediaById[$id]] = true;
    }

    /**
     * @param array<int, string> $mediaById
     * @param array<string, true> $referenced
     */
    private function collectBlockMediaRefs(string $json, array $mediaById, array &$referenced): void
    {
        $json = trim($json);
        if ($json === '' || $json === '[]') {
            return;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return;
        }

        $this->collectMediaIdsFromValue($decoded, $mediaById, $referenced);
    }

    /**
     * @param mixed $value
     * @param array<int, string> $mediaById
     * @param array<string, true> $referenced
     */
    private function collectMediaIdsFromValue(mixed $value, array $mediaById, array &$referenced): void
    {
        if (! is_array($value)) {
            return;
        }

        foreach ($value as $key => $child) {
            if (is_string($key) && preg_match('/(^|_)media_id(_\d+)?$/', $key) === 1) {
                $this->collectMediaId($child, $mediaById, $referenced);
            }
            if (is_array($child)) {
                $this->collectMediaIdsFromValue($child, $mediaById, $referenced);
            }
        }
    }
}
