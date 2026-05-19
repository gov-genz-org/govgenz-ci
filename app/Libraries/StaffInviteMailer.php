<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * E-mail d’invitation au back-office staff (HTML + texte brut).
 */
final class StaffInviteMailer
{
    public static function sendInvitation(string $toEmail, string $plainToken, string $role): bool
    {
        helper(['email']);

        $config    = config('Email');
        $fromEmail = self::resolveFromEmail(trim($config->fromEmail));
        $fromName  = trim($config->fromName) !== '' ? trim($config->fromName) : 'GoV Gen Z';

        if ($fromEmail === '' || ! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            log_message('warning', 'StaffInviteMailer: expéditeur ou destinataire invalide.');

            return false;
        }

        $roleLabel   = $role === 'admin' ? 'administrateur' : 'éditeur';
        $hours       = StaffInvite::expiryHours();
        $expiryLabel = $hours <= 24 ? '24 heures' : ((int) ceil($hours / 24) . ' jours');
        $setupUrl    = StaffInvite::inviteUrl($plainToken);
        $siteName    = trim($fromName) !== '' ? $fromName : 'GoV Gen Z Madagascar';

        $subject = 'Invitation au back-office — ' . $siteName;

        $viewData = [
            'roleLabel'   => $roleLabel,
            'setupUrl'    => $setupUrl,
            'expiryLabel' => $expiryLabel,
            'logoUrl'     => email_brand_logo_url(),
            'siteName'    => $siteName,
        ];

        $html  = view('emails/staff_invite', $viewData);
        $plain = self::buildPlainText($roleLabel, $setupUrl, $expiryLabel, $siteName);

        try {
            $email = service('email');
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($toEmail);
            $email->setSubject($subject);
            $email->setMailType('html');
            $email->setMessage($html);
            $email->setAltMessage($plain);

            if (! $email->send()) {
                log_message('error', 'StaffInviteMailer: échec — ' . $email->printDebugger(['headers']));

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'StaffInviteMailer: ' . $e->getMessage());

            return false;
        }
    }

    private static function buildPlainText(
        string $roleLabel,
        string $setupUrl,
        string $expiryLabel,
        string $siteName,
    ): string {
        $lines = [
            'Bonjour,',
            '',
            'Un compte ' . $roleLabel . ' a été créé pour vous sur le back-office ' . $siteName . '.',
            '',
            'Pour confirmer votre adresse e-mail et choisir votre mot de passe, ouvrez ce lien :',
            $setupUrl,
            '',
            'Ce lien est valable ' . $expiryLabel . '.',
            '',
            'Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.',
            '',
            '— ' . $siteName,
        ];

        return implode("\n", $lines);
    }

    private static function resolveFromEmail(string $configured): string
    {
        if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_EMAIL)) {
            return $configured;
        }

        $smtpUser = trim((string) env('email.SMTPUser', ''));

        return filter_var($smtpUser, FILTER_VALIDATE_EMAIL) ? $smtpUser : '';
    }
}
