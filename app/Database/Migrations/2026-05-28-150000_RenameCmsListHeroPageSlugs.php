<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Bandeaux de listes : slugs alignés sur les URLs publiques (press, projects, positions).
 */
class RenameCmsListHeroPageSlugs extends Migration
{
    /** @var array<int, array{0: string, 1: string, 2: string}> */
    private const RENAMES = [
        ['presse-programme', 'fr', 'press'],
        ['press-program', 'en', 'press'],
        ['projets-programme', 'fr', 'projects'],
        ['projects-program', 'en', 'projects'],
        ['positions-programme', 'fr', 'positions'],
        ['positions-program', 'en', 'positions'],
    ];

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        foreach (self::RENAMES as [$from, $locale, $to]) {
            $this->renameSlugIfFree($from, $locale, $to);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $revert = [
            ['press', 'fr', 'presse-programme'],
            ['press', 'en', 'press-program'],
            ['projects', 'fr', 'projets-programme'],
            ['projects', 'en', 'projects-program'],
            ['positions', 'fr', 'positions-programme'],
            ['positions', 'en', 'positions-program'],
        ];

        foreach ($revert as [$from, $locale, $to]) {
            $this->renameSlugIfFree($from, $locale, $to);
        }
    }

    private function renameSlugIfFree(string $from, string $locale, string $to): void
    {
        $row = $this->db->table('cms_pages')
            ->where('slug', $from)
            ->where('locale', $locale)
            ->get()
            ->getFirstRow('array');

        if ($row === null) {
            return;
        }

        $targetTaken = $this->db->table('cms_pages')
            ->where('slug', $to)
            ->where('locale', $locale)
            ->where('id !=', (int) ($row['id'] ?? 0))
            ->countAllResults() > 0;

        if ($targetTaken) {
            return;
        }

        $this->db->table('cms_pages')
            ->where('id', (int) $row['id'])
            ->update(['slug' => $to, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
