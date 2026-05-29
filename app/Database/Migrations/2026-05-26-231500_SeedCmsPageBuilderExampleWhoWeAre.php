<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Page d'exemple en blocs pour valider la migration d'une page type "Qui sommes-nous".
 */
class SeedCmsPageBuilderExampleWhoWeAre extends Migration
{
    private const SLUG = 'cp-quis-nous-sommes';
    private const LOCALE = 'fr';
    private const TRANSLATION_GROUP = 'cp-quis-nous-sommes-blocks-example';

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
            'title'             => 'CP QUIS NOUS SOMMES',
            'hero_overline'     => 'Exemple Page Builder',
            'hero_title'        => 'CP QUIS NOUS SOMMES',
            'hero_lead'         => 'Copie de validation de la page Qui sommes-nous, construite avec les blocs CMS pour éviter le HTML.',
            'body_html'         => '',
            'content_mode'      => 'blocks',
            'body_blocks'       => json_encode($this->blocks(), JSON_UNESCAPED_UNICODE),
            'status'            => 'published',
            'meta_title'        => 'CP QUIS NOUS SOMMES — GoV Gen Z Madagascar',
            'meta_description'  => 'Page exemple en blocs CMS reprenant la structure Qui sommes-nous.',
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
                'type'       => 'cards_grid',
                'variant'    => 'circle_cards',
                'source'     => 'Source · Étude GoV Gen Z Madagascar 2026',
                'cards'      => [
                    [
                        'value'       => '12,44',
                        'unit'        => 'M',
                        'title'       => 'Enfants',
                        'subtitle'    => '0-17 ans · 48,5%',
                        'description' => 'L\'avenir du pays se joue dès aujourd\'hui',
                    ],
                    [
                        'value'       => '8,68',
                        'unit'        => 'M',
                        'title'       => 'Jeunesse',
                        'subtitle'    => '14-30 ans · 33,8%',
                        'description' => 'Coeur de la mobilisation et de la construction',
                    ],
                    [
                        'value'       => '∞',
                        'title'       => 'Relève',
                        'subtitle'    => 'Générations futures',
                        'description' => 'Bâtir un héritage qui les protège',
                    ],
                    [
                        'value'       => '∞',
                        'title'       => 'Diaspora',
                        'subtitle'    => 'Malgaches du monde',
                        'description' => 'Compétences, mentorat, plaidoyer international',
                    ],
                    [
                        'value'       => '∞',
                        'title'       => 'Sympathisants',
                        'subtitle'    => 'Celles et ceux qui soutiennent',
                        'description' => 'Toutes les énergies bienveillantes',
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
