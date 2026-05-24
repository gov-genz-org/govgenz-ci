<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Support\CmsLegalMentionsBodies;
use CodeIgniter\Database\Migration;

/**
 * Renseigne hero_* sur les pages mentions-legales existantes (évite le doublon titre/chapô dans le corps HTML).
 */
class SyncCmsLegalMentionsHeroFields extends Migration
{
    private const SLUG = 'mentions-legales';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $this->syncLocale('fr', CmsLegalMentionsBodies::heroFr(), CmsLegalMentionsBodies::fr());
        $this->syncLocale('en', CmsLegalMentionsBodies::heroEn(), CmsLegalMentionsBodies::en());
    }

    public function down(): void
    {
        // Pas de retour arrière : les champs hero peuvent rester renseignés.
    }

    /**
     * @param array{hero_overline: string, hero_title: string, hero_lead: string} $hero
     */
    private function syncLocale(string $locale, array $hero, string $bodyHtml): void
    {
        $row = $this->db->table('cms_pages')
            ->where('slug', self::SLUG)
            ->where('locale', $locale)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return;
        }

        $update = [
            'hero_overline' => $hero['hero_overline'],
            'hero_title'    => $hero['hero_title'],
            'hero_lead'     => $hero['hero_lead'],
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $currentBody = trim((string) ($row['body_html'] ?? ''));
        if ($currentBody === '' || str_contains($currentBody, 'section__header')) {
            $update['body_html'] = $bodyHtml;
        }

        $this->db->table('cms_pages')
            ->where('id', (int) ($row['id'] ?? 0))
            ->update($update);
    }
}
