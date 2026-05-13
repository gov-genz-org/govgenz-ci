<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Référentiel global des secteurs (Join, page Secteurs, module Projets).
 * Si project_sectors existe encore (anciennes installs), conversion des codes + suppression.
 */
class CreateSectorsTable extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('sectors')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                ],
                'label_fr' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'label_en' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'contact_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 190,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'unsigned'   => true,
                    'default'    => 1,
                ],
                'sort_order' => [
                    'type'       => 'SMALLINT',
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('code');
            $this->forge->addKey('sort_order');
            $this->forge->addKey('is_active');
            $this->forge->createTable('sectors');

            $now = date('Y-m-d H:i:s');
            $rows = [
                ['code' => 'legal', 'label_fr' => 'Justice · Gouvernance · Anti-corruption', 'label_en' => 'Justice · Governance · Anti-corruption', 'contact_email' => 'legal@govgenz.org', 'sort_order' => 10],
                ['code' => 'economy', 'label_fr' => 'Finances publiques · Commerce · Emploi', 'label_en' => 'Public finance · Trade · Employment', 'contact_email' => 'economy@govgenz.org', 'sort_order' => 20],
                ['code' => 'food', 'label_fr' => 'Agriculture · Pêche · Souveraineté alimentaire', 'label_en' => 'Agriculture · Fisheries · Food sovereignty', 'contact_email' => 'food@govgenz.org', 'sort_order' => 30],
                ['code' => 'energy', 'label_fr' => 'Énergies renouvelables · Solaire · Éolien', 'label_en' => 'Renewable energy · Solar · Wind', 'contact_email' => 'energy@govgenz.org', 'sort_order' => 40],
                ['code' => 'water', 'label_fr' => 'Eau et assainissement · Accès · Qualité', 'label_en' => 'Water and sanitation · Access · Quality', 'contact_email' => 'water@govgenz.org', 'sort_order' => 50],
                ['code' => 'education', 'label_fr' => 'Formation · Recherche · Innovation', 'label_en' => 'Training · Research · Innovation', 'contact_email' => 'education@govgenz.org', 'sort_order' => 60],
                ['code' => 'health', 'label_fr' => 'Santé · Nutrition · Protection sociale', 'label_en' => 'Health · Nutrition · Social protection', 'contact_email' => 'health@govgenz.org', 'sort_order' => 70],
                ['code' => 'infrastructure', 'label_fr' => 'Transport · Désenclavement', 'label_en' => 'Transport · Connectivity', 'contact_email' => 'infrastructure@govgenz.org', 'sort_order' => 80],
                ['code' => 'digital', 'label_fr' => 'Numérique · Données · IA', 'label_en' => 'Digital · Data · AI', 'contact_email' => 'digital@govgenz.org', 'sort_order' => 90],
                ['code' => 'territories', 'label_fr' => 'Décentralisation · Foncier · Logement', 'label_en' => 'Decentralisation · Land · Housing', 'contact_email' => 'territories@govgenz.org', 'sort_order' => 100],
                ['code' => 'environment', 'label_fr' => 'Climat · Ressources naturelles', 'label_en' => 'Climate · Natural resources', 'contact_email' => 'environment@govgenz.org', 'sort_order' => 110],
                ['code' => 'mines', 'label_fr' => 'Ressources minières · Traçabilité', 'label_en' => 'Mineral resources · Traceability', 'contact_email' => 'mines@govgenz.org', 'sort_order' => 120],
                ['code' => 'security', 'label_fr' => 'Sécurité civile · Gestion crise', 'label_en' => 'Civil security · Crisis management', 'contact_email' => 'security@govgenz.org', 'sort_order' => 130],
                ['code' => 'citizen', 'label_fr' => 'Jeunesse · Culture · Diaspora', 'label_en' => 'Youth · Culture · Diaspora', 'contact_email' => 'citizen@govgenz.org', 'sort_order' => 140],
            ];
            foreach ($rows as &$r) {
                $r['is_active']   = 1;
                $r['created_at']  = $now;
                $r['updated_at']  = $now;
            }
            unset($r);
            $this->db->table('sectors')->insertBatch($rows);
        }

        if ($this->db->tableExists('project_sectors')) {
            $upperToLower = [
                'LEGAL'          => 'legal',
                'ECONOMY'        => 'economy',
                'FOOD'           => 'food',
                'ENERGY'         => 'energy',
                'WATER'          => 'water',
                'EDUCATION'      => 'education',
                'HEALTH'         => 'health',
                'INFRASTRUCTURE' => 'infrastructure',
                'DIGITAL'        => 'digital',
                'TERRITORIES'    => 'territories',
                'ENVIRONMENT'    => 'environment',
                'MINES'          => 'mines',
                'SECURITY'       => 'security',
                'CITIZEN'        => 'citizen',
            ];

            $projects = $this->db->table('project_projects')->get()->getResultArray();
            foreach ($projects as $p) {
                $csv = trim((string) ($p['sectors_csv'] ?? ''));
                if ($csv === '') {
                    continue;
                }
                $parts = array_map('trim', explode(',', $csv));
                $out   = [];
                foreach ($parts as $part) {
                    if ($part === '') {
                        continue;
                    }
                    $u = strtoupper($part);
                    if (isset($upperToLower[$u])) {
                        $out[] = $upperToLower[$u];
                    } else {
                        $out[] = strtolower($part);
                    }
                }
                $out   = array_values(array_unique($out));
                $newCsv = implode(',', $out);
                if ($newCsv !== $csv) {
                    $this->db->table('project_projects')->where('id', (int) $p['id'])->update(['sectors_csv' => $newCsv]);
                }
            }

            $this->forge->dropTable('project_sectors', true);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('sectors')) {
            $this->forge->dropTable('sectors', true);
        }
    }
}
