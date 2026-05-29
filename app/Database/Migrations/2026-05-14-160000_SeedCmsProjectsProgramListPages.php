<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pages CMS pour le bandeau de /projects (sur-titre, titre, chapô) — textes par défaut en base.
 */
class SeedCmsProjectsProgramListPages extends Migration
{
    private const TRANSLATION_GROUP = 'projects-program-list';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->insertIfMissing('projects', 'fr', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Liste programme projets',
            'hero_overline'     => 'Programme Paikady Taninjanaka',
            'hero_title'        => 'Cartographie des projets',
            'hero_lead'         => 'Initiatives publiées par l’équipe GoV Gen Z Madagascar : statut, secteurs d’intervention, bénévolat et territoires. Les fiches détaillées reprennent le même gabarit que la version statique de référence.',
            'meta_title'        => 'Projets — GoV Gen Z Madagascar',
            'meta_description'  => 'Programme projets GoV Gen Z Madagascar — cartographie des initiatives publiées.',
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

        $this->insertIfMissing('projects', 'en', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Project programme listing',
            'hero_overline'     => 'Paikady Taninjanaka programme',
            'hero_title'        => 'Project mapping',
            'hero_lead'         => 'Initiatives published by the GoV Gen Z Madagascar team: status, sectors of intervention, volunteering and territories. Detailed project pages follow the same layout as the static reference.',
            'meta_title'        => 'Projects — GoV Gen Z Madagascar',
            'meta_description'  => 'GoV Gen Z Madagascar project programme — mapping of published initiatives.',
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
