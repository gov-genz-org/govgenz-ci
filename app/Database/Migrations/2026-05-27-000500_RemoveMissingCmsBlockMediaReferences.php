<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsMediaStorage;
use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Nettoie les IDs médias de blocs qui pointent vers une ligne/fichier absent.
 */
class RemoveMissingCmsBlockMediaReferences extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $validMediaIds = $this->validMediaIds();
        $pages = $this->db->table('cms_pages')->get()->getResultArray();

        foreach ($pages as $page) {
            $updates = [];

            $heroImageId = (int) ($page['hero_image_id'] ?? 0);
            if ($heroImageId > 0 && ! isset($validMediaIds[$heroImageId])) {
                $updates['hero_image_id'] = null;
                $updates['hero_image_alt'] = null;
            }

            $json = trim((string) ($page['body_blocks'] ?? ''));
            if ($json !== '' && $json !== '[]') {
                $blocks = json_decode($json, true);
                if (is_array($blocks) && $this->removeMissingMediaIds($blocks, $validMediaIds)) {
                    $updates['body_blocks'] = json_encode($blocks, JSON_UNESCAPED_UNICODE);
                }
            }

            if ($updates === []) {
                continue;
            }

            $updates['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('cms_pages')
                ->where('id', (int) ($page['id'] ?? 0))
                ->update($updates);

            CmsPublishedPageCache::forget((string) ($page['locale'] ?? 'fr'), (string) ($page['slug'] ?? ''));
        }
    }

    public function down(): void
    {
        // Pas de restauration : les fichiers absents ne peuvent pas être recréés.
    }

    /**
     * @return array<int, true>
     */
    private function validMediaIds(): array
    {
        if (! $this->db->tableExists('cms_media')) {
            return [];
        }

        $out = [];
        foreach ($this->db->table('cms_media')->get()->getResultArray() as $row) {
            $id = (int) ($row['id'] ?? 0);
            $fn = (string) ($row['stored_filename'] ?? '');
            if ($id > 0 && $fn !== '' && CmsMediaStorage::fileExists($fn)) {
                $out[$id] = true;
            }
        }

        return $out;
    }

    /**
     * @param mixed $value
     * @param array<int, true> $validMediaIds
     */
    private function removeMissingMediaIds(mixed &$value, array $validMediaIds): bool
    {
        if (! is_array($value)) {
            return false;
        }

        $changed = false;
        foreach (array_keys($value) as $key) {
            $child =& $value[$key];
            if (is_string($key) && preg_match('/(^|_)media_id(_\d+)?$/', $key) === 1) {
                $id = (int) $child;
                if ($id > 0 && ! isset($validMediaIds[$id])) {
                    unset($value[$key]);
                    $altKey = str_replace('media_id', 'media_alt', $key);
                    unset($value[$altKey]);
                    $changed = true;
                }
                continue;
            }

            if (is_array($child) && $this->removeMissingMediaIds($child, $validMediaIds)) {
                $changed = true;
            }
        }
        unset($child);

        return $changed;
    }
}
