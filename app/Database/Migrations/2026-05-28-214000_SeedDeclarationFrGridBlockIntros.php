<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Page CMS declaration (FR) : intros + bandeaux sur structures_grid et sectors_grid.
 */
class SeedDeclarationFrGridBlockIntros extends Migration
{
    /** @var array<string, mixed> */
    private const STRUCTURES_INTRO = [
        'kicker'          => 'Organigramme',
        'title'           => 'Structure organisationnelle',
        'lead'            => 'Un noyau exécutif central appuyé par 7 départements spécialisés — Programme Paikady Taninjanaka.',
        'layout'          => 'dept',
        'banner_title'    => 'Structure organisationnelle — GoV Gen Z Madagascar',
        'banner_subtitle' => '7 départements · Programme Paikady Taninjanaka',
    ];

    /** @var array<string, mixed> */
    private const SECTORS_INTRO = [
        'kicker'          => 'Équipes de terrain',
        'title'           => '14 équipes sectorielles',
        'lead'            => 'Chaque secteur clé de Madagascar est couvert par une équipe dédiée — bâtir, innover, servir le peuple.',
        'layout'          => 'compact',
        'banner_title'    => 'Équipes sectorielles — GoV Gen Z Madagascar',
        'banner_subtitle' => '14 secteurs · Madagascar',
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $row = $this->db->table('cms_pages')
            ->where('slug', 'declaration')
            ->where('locale', 'fr')
            ->get()
            ->getRowArray();

        if ($row === null) {
            return;
        }

        $raw = trim((string) ($row['body_blocks'] ?? ''));
        if ($raw === '' || $raw === '[]') {
            return;
        }

        $blocks = json_decode($raw, true);
        if (! is_array($blocks)) {
            return;
        }

        $changed = false;
        foreach ($blocks as $idx => $blk) {
            if (! is_array($blk)) {
                continue;
            }

            $type = strtolower(trim((string) ($blk['type'] ?? '')));
            if ($type === 'structures_grid') {
                $blocks[$idx] = $this->applyIntro($blk, self::STRUCTURES_INTRO);
                $changed      = true;
            } elseif ($type === 'sectors_grid') {
                $blocks[$idx] = $this->applyIntro($blk, self::SECTORS_INTRO);
                $changed      = true;
            }
        }

        if (! $changed) {
            return;
        }

        $this->db->table('cms_pages')->where('id', (int) ($row['id'] ?? 0))->update([
            'body_blocks' => json_encode($blocks, JSON_UNESCAPED_UNICODE),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        CmsPublishedPageCache::forget('fr', 'declaration');
    }

    public function down(): void
    {
    }

    /**
     * @param array<string, mixed> $blk
     * @param array<string, mixed> $intro
     * @return array<string, mixed>
     */
    private function applyIntro(array $blk, array $intro): array
    {
        foreach ($intro as $key => $value) {
            if ($key === 'layout') {
                $blk['layout'] = $value;

                continue;
            }

            $blk[$key] = $value;
        }

        return $blk;
    }
}
