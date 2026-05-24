<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Règles communes connexion staff (mot de passe minimal selon l’environnement).
 */
class StaffAuthPolicy
{
    public static function loginPasswordMinLength(): int
    {
        return ENVIRONMENT === 'production' ? 12 : 6;
    }
}
