<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Projets FR / EN : locale + groupe de traduction, slug unique par langue (comme cms_pages).
 */
class AddLocaleToProjectProjects extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('project_projects')) {
            return;
        }

        if (! $this->db->fieldExists('locale', 'project_projects')) {
            $this->dropUniqueIndexOnColumnOnly('project_projects', 'slug');
            $this->forge->addColumn('project_projects', [
                'locale' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 8,
                    'default'    => 'fr',
                    'after'      => 'slug',
                ],
                'translation_group' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'after'      => 'locale',
                ],
            ]);
        } elseif (! $this->db->fieldExists('translation_group', 'project_projects')) {
            $this->forge->addColumn('project_projects', [
                'translation_group' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                    'after'      => 'locale',
                ],
            ]);
        }

        $this->db->query('UPDATE project_projects SET translation_group = CAST(id AS CHAR) WHERE translation_group IS NULL OR translation_group = \'\'');

        if (! $this->indexExists('project_projects', 'project_projects_slug_locale')) {
            $this->dropUniqueIndexOnColumnOnly('project_projects', 'slug');
            $this->forge->addKey(['slug', 'locale'], false, true, 'project_projects_slug_locale');
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('project_projects')) {
            return;
        }

        if ($this->indexExists('project_projects', 'project_projects_slug_locale')) {
            $this->forge->dropKey('project_projects', 'project_projects_slug_locale');
        }

        if ($this->db->fieldExists('locale', 'project_projects')) {
            $this->forge->dropColumn('project_projects', ['locale', 'translation_group']);
        } elseif ($this->db->fieldExists('translation_group', 'project_projects')) {
            $this->forge->dropColumn('project_projects', 'translation_group');
        }

        $this->forge->addKey('slug', false, true);
    }

    private function indexExists(string $table, string $keyName): bool
    {
        if (! preg_match('/^[a-z0-9_]+$/', $table)) {
            return false;
        }

        $rows = $this->db->query(
            'SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?',
            [$keyName],
        )->getResultArray();

        return $rows !== [];
    }

    /**
     * Supprime un index UNIQUE dont la seule colonne est $column (ex. slug seul).
     */
    private function dropUniqueIndexOnColumnOnly(string $table, string $column): void
    {
        $rows = $this->db->query('SHOW INDEX FROM `' . $table . '`')->getResultArray();

        /** @var array<string, list<string>> $colsByKey */
        $colsByKey = [];

        foreach ($rows as $row) {
            $key = (string) ($row['Key_name'] ?? '');
            if ($key === '' || $key === 'PRIMARY') {
                continue;
            }

            $nonUnique = (int) ($row['Non_unique'] ?? 1);
            if ($nonUnique !== 0) {
                continue;
            }

            $col = (string) ($row['Column_name'] ?? '');
            $seq = (int) ($row['Seq_in_index'] ?? 1);
            $colsByKey[$key][$seq] = $col;
        }

        foreach ($colsByKey as $keyName => $seqCols) {
            ksort($seqCols);
            $cols = array_values($seqCols);
            if ($cols === [$column]) {
                $this->db->query('ALTER TABLE `' . $table . '` DROP INDEX `' . $keyName . '`');

                return;
            }
        }
    }
}
