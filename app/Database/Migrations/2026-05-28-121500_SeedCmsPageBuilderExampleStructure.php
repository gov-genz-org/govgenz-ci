<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Page d'exemple en blocs pour valider la migration d'une page type "Structure".
 */
class SeedCmsPageBuilderExampleStructure extends Migration
{
    private const SLUG = 'cp-structure';
    private const LOCALE = 'fr';
    private const TRANSLATION_GROUP = 'cp-structure-blocks-example';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $exists = $this->db->table('cms_pages')
            ->where('slug', self::SLUG)
            ->where('locale', self::LOCALE)
            ->get()
            ->getFirstRow() !== null;

        if ($exists) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $this->insertFiltered([
            'slug'              => self::SLUG,
            'locale'            => self::LOCALE,
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'CP STRUCTURE',
            'hero_overline'     => 'Exemple Page Builder',
            'hero_title'        => 'CP STRUCTURE',
            'hero_lead'         => 'Copie de validation de la page Structure, construite avec les blocs CMS pour éviter le HTML.',
            'body_html'         => '',
            'content_mode'      => 'blocks',
            'body_blocks'       => json_encode($this->blocks(), JSON_UNESCAPED_UNICODE),
            'status'            => 'published',
            'meta_title'        => 'CP STRUCTURE — GoV Gen Z Madagascar',
            'meta_description'  => 'Page exemple en blocs CMS reprenant la structure de la page Structure.',
            'layout_key'        => 'full',
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
            ->where('slug', self::SLUG)
            ->where('locale', self::LOCALE)
            ->where('translation_group', self::TRANSLATION_GROUP)
            ->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function blocks(): array
    {
        return [
            [
                'type'          => 'organization_hub',
                'core_label'    => 'NOYAU EXÉCUTIF CENTRAL',
                'core_subtitle' => 'Coordination · Sécurité · Vision · Décision',
                'core_href'     => 'mailto:contact@govgenz.org',
                'items'         => [
                    [
                        'name'     => 'COORDINATION',
                        'subtitle' => 'Exécutifs · Sectorielle · Régions · Diaspora',
                        'href'     => 'mailto:coordination@govgenz.org',
                    ],
                    [
                        'name'     => 'SÉCURITÉ',
                        'subtitle' => 'Préventive & corrective · Juridique · Tech · Terrain',
                        'href'     => 'mailto:safety@govgenz.org',
                    ],
                    [
                        'name'     => 'COMMUNICATION',
                        'subtitle' => 'Presse · Branding · Réseaux sociaux',
                        'href'     => 'mailto:communication@govgenz.org',
                    ],
                    [
                        'name'     => 'MOBILISATION',
                        'subtitle' => 'Terrains · Volontaires · Diaspora',
                        'href'     => 'mailto:mobilization@govgenz.org',
                    ],
                    [
                        'name'     => 'PROSPECTIVE',
                        'subtitle' => 'Data · Études · Projections · IA',
                        'href'     => 'mailto:strategy@govgenz.org',
                    ],
                    [
                        'name'     => 'SECTEURS',
                        'subtitle' => 'Éducation · Santé · Économie · Agriculture · Justice · Environnement',
                        'href'     => 'mailto:sectors@govgenz.org',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertFiltered(array $row): void
    {
        $fieldData = $this->db->getFieldData('cms_pages');
        $names = [];
        foreach ($fieldData as $field) {
            $name = is_object($field) ? ($field->name ?? null) : ($field['name'] ?? null);
            if (is_string($name) && $name !== '' && $name !== 'id') {
                $names[] = $name;
            }
        }

        $out = [];
        foreach ($names as $name) {
            if (array_key_exists($name, $row)) {
                $out[$name] = $row[$name];
            }
        }

        $this->db->table('cms_pages')->insert($out);
    }
}

