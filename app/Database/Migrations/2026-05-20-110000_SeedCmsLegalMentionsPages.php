<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsLegalMentionsBodies;
use CodeIgniter\Database\Migration;

/**
 * Pages CMS mentions légales / politique cookies (FR + EN) — slug analytics.privacyPageSlug.
 */
class SeedCmsLegalMentionsPages extends Migration
{
    private const TRANSLATION_GROUP = 'legal-mentions';

    private const SLUG = 'mentions-legales';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $heroFr = CmsLegalMentionsBodies::heroFr();
        $heroEn = CmsLegalMentionsBodies::heroEn();

        $this->insertIfMissing(self::SLUG, 'fr', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Mentions légales',
            'hero_overline'     => $heroFr['hero_overline'],
            'hero_title'        => $heroFr['hero_title'],
            'hero_lead'         => $heroFr['hero_lead'],
            'meta_title'        => 'Mentions légales · GoV Gen Z Madagascar',
            'meta_description'  => 'Éditeur du site, cookies, mesure d’audience (Google Analytics) et traitement des données des formulaires publics.',
            'body_html'         => CmsLegalMentionsBodies::fr(),
            'status'            => 'published',
            'layout_key'        => 'full',
            'content_mode'      => 'html',
            'body_blocks'       => null,
            'hero_image_id'     => null,
            'hero_image_alt'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $this->insertIfMissing(self::SLUG, 'en', [
            'translation_group' => self::TRANSLATION_GROUP,
            'title'             => 'Legal notice & privacy',
            'hero_overline'     => $heroEn['hero_overline'],
            'hero_title'        => $heroEn['hero_title'],
            'hero_lead'         => $heroEn['hero_lead'],
            'meta_title'        => 'Legal notice · GoV Gen Z Madagascar',
            'meta_description'  => 'Site publisher, cookies, audience measurement (Google Analytics) and personal data from public forms.',
            'body_html'         => CmsLegalMentionsBodies::en(),
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
