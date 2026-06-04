<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedStructureUnitsFromDeclarationDepts extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('structure_units')) {
            return;
        }

        if ($this->db->table('structure_units')->countAllResults() > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $rows = [
            ['coordination', 'Coordination', 'Coordination', 'Direction générale, alignement entre équipes, pilotage des décisions stratégiques du mouvement.', 'General management, team alignment, strategic decision-making for the movement.', 'coordination@govgenz.org', 10],
            ['securite', 'Sécurité', 'Security', 'Protection des membres, gestion des risques, sécurité des données et continuité des opérations.', 'Member protection, risk management, data security and operational continuity.', 'securite@govgenz.org', 20],
            ['communication', 'Communication', 'Communication', 'Relations presse, réseaux sociaux, contenus publics, image institutionnelle du mouvement.', 'Press relations, social media, public content, institutional image.', 'communication@govgenz.org', 30],
            ['partenariats', 'Partenariats', 'Partnerships', 'Relations avec les PTF, organisations internationales, ambassades et alliances citoyennes.', 'Relations with donors, international organizations, embassades and citizen alliances.', 'partnerships@govgenz.org', 40],
            ['rh', 'Ressources humaines', 'Human resources', 'Recrutement des volontaires, formation, bien-être des membres, gestion des compétences.', 'Volunteer recruitment, training, member wellbeing, skills management.', 'rh@govgenz.org', 50],
            ['projets', 'Project Management', 'Project management', 'Suivi opérationnel des projets, coordination des équipes terrain, reporting et indicateurs.', 'Operational project follow-up, field team coordination, reporting and indicators.', 'projets@govgenz.org', 60],
            ['finances', 'Finances', 'Finance', 'Gestion transparente des ressources, budgets, audits internes et reporting financier.', 'Transparent resource management, budgets, internal audits and financial reporting.', 'finances@govgenz.org', 70],
        ];

        foreach ($rows as $row) {
            $this->db->table('structure_units')->insert([
                'code'            => $row[0],
                'unit_role'       => 'function',
                'title_fr'        => $row[1],
                'title_en'        => $row[2],
                'description_fr'  => $row[3],
                'description_en'  => $row[4],
                'contact_email'   => $row[5],
                'sort_order'      => $row[6],
                'is_active'       => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('structure_units')) {
            $this->db->table('structure_units')->truncate();
        }
    }
}
