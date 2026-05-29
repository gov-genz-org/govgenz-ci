<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pages CMS pour le bandeau de /press (sur-titre, titre, chapô) — textes par défaut en base.
 */
class SeedCmsPressListPages extends Migration
{
    private const TRANSLATION_GROUP = 'press-program-list';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->insertIfMissing('press', 'fr', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Liste presse',
            'hero_overline'     => 'MÉDIAS',
            'hero_title'        => 'Presse',
            'hero_lead'         => 'Communiqués et actualités publiés par GovGenZ Madagascar.',
            'meta_title'        => 'Presse — GovGenZ',
            'meta_description'  => 'Communiqués et actualités de presse publiés par GovGenZ Madagascar.',
            'body_html'         => '',
            'status'            => 'published',
            'layout_key'        => 'full',
            'content_mode'      => 'html',
            'body_blocks'       => null,
            'hero_image_id'     => null,
            'hero_image_alt'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $this->insertIfMissing('press', 'en', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Press listing',
            'hero_overline'     => 'MEDIA',
            'hero_title'        => 'Press',
            'hero_lead'         => 'Statements and news published by GovGenZ Madagascar.',
            'meta_title'        => 'Press — GovGenZ',
            'meta_description'  => 'Press releases and news published by GovGenZ Madagascar.',
            'body_html'         => '',
            'status'            => 'published',
            'layout_key'        => 'full',
            'content_mode'      => 'html',
            'body_blocks'       => null,
            'hero_image_id'     => null,
            'hero_image_alt'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }

    public function down(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $this->db->table('cms_pages')
            ->where('translation_group', self::TRANSLATION_GROUP)
            ->delete();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertIfMissing(string $slug, string $locale, array $row): void
    {
        $exists = $this->db->table('cms_pages')
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->get()
            ->getFirstRow() !== null;

        if ($exists) {
            return;
        }

        $row['slug']   = $slug;
        $row['locale'] = $locale;

        $fieldData = $this->db->getFieldData('cms_pages');
        $names     = [];
        foreach ($fieldData as $f) {
            $n = is_object($f) ? ($f->name ?? null) : ($f['name'] ?? null);
            if (is_string($n) && $n !== '' && $n !== 'id') {
                $names[] = $n;
            }
        }

        $out = [];
        foreach ($names as $name) {
            if (! array_key_exists($name, $row)) {
                continue;
            }
            $out[$name] = $row[$name];
        }

        $this->db->table('cms_pages')->insert($out);
    }
}
