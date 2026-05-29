<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedCmsPositionsListPages extends Migration
{
    private const TRANSLATION_GROUP = 'positions-program-list';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->insertIfMissing('positions', 'fr', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Liste programme positions',
            'hero_overline'     => 'Avis · Analyses · Alertes · Solutions',
            'hero_title'        => 'La voix de la jeunesse documentée et argumentée.',
            'hero_lead'         => 'GoV Gen Z Madagascar analyse l\'actualité malgache, alerte sur les manquements et propose des alternatives concrètes avec budgets et indicateurs mesurables.',
            'meta_title'        => 'Nos positions — GoV Gen Z Madagascar',
            'meta_description'  => 'Positions, analyses et alertes de GoV Gen Z Madagascar — avis documentés sur l\'actualité.',
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

        $this->insertIfMissing('positions', 'en', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Positions programme listing',
            'hero_overline'     => 'Opinions · Analysis · Alerts · Solutions',
            'hero_title'        => 'Youth voice — documented and argued.',
            'hero_lead'         => 'GoV Gen Z Madagascar analyses Malagasy current affairs, flags gaps and proposes concrete alternatives with budgets and measurable indicators.',
            'meta_title'        => 'Our positions — GoV Gen Z Madagascar',
            'meta_description'  => 'GoV Gen Z Madagascar positions, analysis and alerts on current affairs.',
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
