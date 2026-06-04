<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Libraries\DeclarationProgramPage;
use CodeIgniter\Database\Migration;

/**
 * Corps CMS Déclaration : stats en tête, sans CTA / trust dupliqués (shell programme).
 */
class FixCmsDeclarationPagesProgramShell extends Migration
{
    private const TRANSLATION_GROUP = 'declaration-program-page';

    public function up(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $rows = $this->db->table('cms_pages')
            ->where('translation_group', self::TRANSLATION_GROUP)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $raw = trim((string) ($row['body_blocks'] ?? ''));
            if ($raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                continue;
            }

            $partition = DeclarationProgramPage::partitionBlocks($decoded);
            $body      = $partition['body'];
            if ($partition['stats'] !== []) {
                array_unshift($body, [
                    'type'  => 'stats_grid',
                    'stats' => $partition['stats'],
                ]);
            }

            $this->db->table('cms_pages')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update([
                    'body_blocks' => json_encode($body, JSON_UNESCAPED_UNICODE),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
        }
    }

    public function down(): void
    {
        // Contenu éditorial : pas de retour arrière automatique.
    }
}
