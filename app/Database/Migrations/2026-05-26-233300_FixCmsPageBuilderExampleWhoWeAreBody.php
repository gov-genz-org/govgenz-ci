<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Retire l'en-tête du corps de la page exemple, déjà porté par les champs hero.
 */
class FixCmsPageBuilderExampleWhoWeAreBody extends Migration
{
    private const SLUG = 'cp-quis-nous-sommes';
    private const LOCALE = 'fr';
    private const TRANSLATION_GROUP = 'cp-quis-nous-sommes-blocks-example';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $row = $this->db->table('cms_pages')
            ->where('slug', self::SLUG)
            ->where('locale', self::LOCALE)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return;
        }

        $this->db->table('cms_pages')
            ->where('id', (int) ($row['id'] ?? 0))
            ->update([
                'title'             => 'CP QUIS NOUS SOMMES',
                'hero_overline'     => 'Exemple Page Builder',
                'hero_title'        => 'CP QUIS NOUS SOMMES',
                'hero_lead'         => 'Copie de validation de la page Qui sommes-nous, construite avec les blocs CMS pour éviter le HTML.',
                'body_html'         => '',
                'content_mode'      => 'blocks',
                'body_blocks'       => json_encode($this->blocks(), JSON_UNESCAPED_UNICODE),
                'layout_key'        => 'full',
                'translation_group' => self::TRANSLATION_GROUP,
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

        CmsPublishedPageCache::forget(self::LOCALE, self::SLUG);
    }

    public function down(): void
    {
        // Pas de restauration fiable : cette migration corrige uniquement l'exemple.
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function blocks(): array
    {
        return [
            [
                'type'    => 'cards_grid',
                'variant' => 'circle_cards',
                'source'  => 'Source · Étude GoV Gen Z Madagascar 2026',
                'cards'   => [
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
}
