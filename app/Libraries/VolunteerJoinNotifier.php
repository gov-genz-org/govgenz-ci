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
    public static function send(array $payload): void
    {
        $config       = config('Email');
        $fromEmail    = self::resolveFromEmail(trim($config->fromEmail));
        $fromName     = trim($config->fromName) !== '' ? trim($config->fromName) : 'Gov Gen Z';
        $recipientTo  = trim((string) env('join.notification.to', 'apps@govgenz.org'));

        if ($fromEmail === '' || $recipientTo === '') {
            log_message('warning', 'VolunteerJoinNotifier: expéditeur ou destinataire manquant — notification ignorée.');

            return;
        }

        helper(['language']);

        $locale = service('request')->getLocale();
        if ($locale !== 'en') {
            $locale = 'fr';
        }

        $subject = lang('Site.join_notify_subject');

        $body = self::buildBodyText($payload, $locale);

        try {
            $email = service('email');
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($recipientTo);
            $email->setReplyTo($payload['email'], $payload['full_name']);
            $email->setSubject($subject);
            $email->setMailType('text');
            $email->setMessage($body);

            if (! $email->send()) {
                log_message('error', 'VolunteerJoinNotifier: échec envoi — ' . $email->printDebugger(['headers']));
            }
        } catch (\Throwable $e) {
            log_message('error', 'VolunteerJoinNotifier: ' . $e->getMessage());
        }
    }

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
    private static function buildBodyText(array $payload, string $locale): string
    {
        $phone   = $payload['phone'] ?? '';
        $message = $payload['message'] ?? '';

        if ($locale === 'en') {
            $lines = [
                'New submission from the Join form.',
                '',
                'Record ID: ' . $payload['id'],
                'Sector: ' . $payload['sector_label'],
                'Name: ' . $payload['full_name'],
                'Email: ' . $payload['email'],
                'Phone: ' . ($phone !== '' ? $phone : '—'),
                '',
                'Message:',
                $message !== '' ? $message : '—',
                '',
                'IP: ' . $payload['ip_address'],
                '',
                'Validation URL (admin): ' . $payload['admin_validation_url'],
            ];
        } else {
            $lines = [
                'Nouvel envoi depuis le formulaire Rejoindre.',
                '',
                'ID en base : ' . $payload['id'],
                'Secteur : ' . $payload['sector_label'],
                'Nom : ' . $payload['full_name'],
                'E-mail : ' . $payload['email'],
                'Téléphone : ' . ($phone !== '' ? $phone : '—'),
                '',
                'Message :',
                $message !== '' ? $message : '—',
                '',
                'IP : ' . $payload['ip_address'],
                '',
                'URL de validation (admin) : ' . $payload['admin_validation_url'],
            ];
        }

        return implode("\n", $lines);
    }

    /** Comme WordPress : sur le serveur, PHP mail() + From déduit du domaine du site si besoin. */
    private static function resolveFromEmail(string $configured): string
    {
        if ($configured !== '') {
            return $configured;
        }

        $host = parse_url((string) config('App')->baseURL, PHP_URL_HOST);

        return is_string($host)
            && $host !== ''
            && ! in_array(strtolower($host), ['localhost', '127.0.0.1'], true)
            ? 'noreply@' . strtolower($host)
            : '';
    }
}
