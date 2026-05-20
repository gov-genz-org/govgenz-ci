<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Notifie l’équipe par e-mail lors d’un envoi du formulaire Rejoindre (stocké déjà en base).
 */
class VolunteerJoinNotifier
{
    /**
     * @param array{
     *   id: int,
     *   sector_label: string,
     *   full_name: string,
     *   email: string,
     *   phone: ?string,
     *   message: ?string,
     *   ip_address: string,
     *   admin_validation_url: string
     * } $payload
     */
    public static function send(array $payload, string $locale = 'fr'): void
    {
        FormModeratorMailer::sendJoinAlert($payload, $locale);
    }
}
