<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Conservée pour compatibilité des environnements déjà migrés.
 * La configuration du menu alignée sur site_govgenz est appliquée par AlignPublicNavWithSiteGovgenz (222000).
 */
class StreamlinePublicMainNav extends Migration
{
    public function up(): void
    {
        // no-op — ancienne logique retirée : elle cassait le jeu de données govgenz du seed 220000.
    }

    public function down(): void
    {
        // no-op
    }
}
