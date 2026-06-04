<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\CmsPublishedPageCache;
use CodeIgniter\Database\Migration;

/**
 * Page CMS declaration (EN) : textes d’intro + bandeaux sur structures_grid et sectors_grid.
 */
class SeedDeclarationEnGridBlockIntros extends Migration
{
    /** @var array<string, mixed> */
    private const STRUCTURES_INTRO = [
        'kicker'          => 'Organization chart',
        'title'           => 'Organizational structure',
        'lead'            => 'A central executive core supported by 7 specialized departments — Paikady Taninjanaka programme.',
        'layout'          => 'dept',
        'banner_title'    => 'Organizational structure — GoV Gen Z Madagascar',
        'banner_subtitle' => '7 departments · Paikady Taninjanaka program',
    ];

    /** @var array<string, mixed> */
    private const SECTORS_INTRO = [
        'kicker'          => 'Field teams',
        'title'           => '14 sector teams',
        'lead'            => 'Each key sector in Madagascar is covered by a dedicated team — build, innovate, serve the people.',
        'layout'          => 'compact',
        'banner_title'    => 'Sector teams — GoV Gen Z Madagascar',
        'banner_subtitle' => '14 sectors · Madagascar',
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $row = $this->db->table('cms_pages')
            ->where('slug', 'declaration')
            ->where('locale', 'en')
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

        CmsPublishedPageCache::forget('en', 'declaration');
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

            $current = trim((string) ($blk[$key] ?? ''));
            if ($current === '') {
                $blk[$key] = $value;
            }
        }

        return $blk;
    }
}
