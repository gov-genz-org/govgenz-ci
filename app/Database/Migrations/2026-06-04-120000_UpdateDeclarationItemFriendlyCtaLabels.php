<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Libellés CTA plus explicites (prod : données déjà seedées avec « Nous soutenir » / « Nous contacter »).
 */
class UpdateDeclarationItemFriendlyCtaLabels extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('declaration_items')) {
            return;
        }

        $map = [
            'plaidoyer-jeunesse-decisions' => ['fr' => 'Soutenir ce plaidoyer', 'en' => 'Support this advocacy'],
            'youth-public-decisions-advocacy' => ['fr' => 'Soutenir ce plaidoyer', 'en' => 'Support this advocacy'],
            'partenariat-institutionnel'      => ['fr' => 'Écrire à l’équipe partenariats', 'en' => 'Email the partnerships team'],
            'institutional-partnership'       => ['fr' => 'Écrire à l’équipe partenariats', 'en' => 'Email the partnerships team'],
        ];

        foreach ($map as $slug => $labels) {
            foreach ($labels as $locale => $label) {
                $this->db->table('declaration_items')
                    ->where('slug', $slug)
                    ->where('locale', $locale)
                    ->update(['cta_label' => $label, 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
    }

    public function down(): void
    {
        // Données éditoriales — pas de retour arrière automatique.
    }
}
