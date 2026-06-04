<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedStructureUnitCoreAndSubtitles extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('structure_units')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        if ($this->db->table('structure_units')->where('code', 'noyau')->countAllResults() === 0) {
            $this->db->table('structure_units')->insert([
                'code'           => 'noyau',
                'unit_role'      => 'core',
                'title_fr'       => 'NOYAU EXÉCUTIF CENTRAL',
                'title_en'       => 'CENTRAL EXECUTIVE CORE',
                'subtitle_fr'    => 'Coordination · Sécurité · Vision · Décision',
                'subtitle_en'    => 'Coordination · Security · Vision · Decision',
                'description_fr' => null,
                'description_en' => null,
                'contact_email'  => 'contact@govgenz.org',
                'sort_order'     => 0,
                'is_active'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        $subs = [
            'coordination'  => ['Exécutifs · Sectorielle · Régions · Diaspora', 'Executives · Sectoral · Regions · Diaspora'],
            'securite'      => ['Préventive & curative · Legal · Tech · Field', 'Preventive & corrective · Legal · Tech · Field'],
            'communication' => ['Stratégie · Contenus · Réseaux · Vulgarisation', 'Strategy · Content · Networks · Outreach'],
            'partenariats'  => ['National et international', 'National and international'],
            'rh'            => ['Recrutement · Onboarding · Formation', 'Recruitment · Onboarding · Training'],
            'projets'       => ['PMO · Suivi · Impact · KPI', 'PMO · Follow-up · Impact · KPI'],
            'finances'      => ['Comptabilité · Levée · Trésorerie', 'Accounting · Fundraising · Treasury'],
        ];

        foreach ($subs as $code => $pair) {
            $this->db->table('structure_units')
                ->where('code', $code)
                ->update([
                    'unit_role'    => 'function',
                    'subtitle_fr'  => $pair[0],
                    'subtitle_en'  => $pair[1],
                    'updated_at'   => $now,
                ]);
        }
    }

    public function down(): void
    {
    }
}
