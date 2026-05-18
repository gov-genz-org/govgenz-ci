<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Notification e-mail — proposition de financement ou d’apport matériel sur une fiche projet.
 */
final class ProjectContributionNotifier
{
    /**
     * @param array<string, string> $fields
     */
    public static function send(
        string $contributionType,
        string $projectTitle,
        string $projectSlug,
        array $fields,
        ?string $adminValidationUrl = null,
    ): void {
        $config      = config('Email');
        $fromEmail   = self::resolveFromEmail(trim($config->fromEmail));
        $fromName    = trim($config->fromName) !== '' ? trim($config->fromName) : 'Gov Gen Z';
        $recipientTo = trim((string) env('join.notification.to', 'apps@govgenz.org'));

        if ($fromEmail === '' || $recipientTo === '') {
            log_message('warning', 'ProjectContributionNotifier: expéditeur ou destinataire manquant.');

            return;
        }

        $replyEmail = trim($fields['contact'] ?? $fields['email'] ?? '');
        $replyName  = trim($fields['donor_name'] ?? '');

        $subject = $contributionType === 'material'
            ? 'Matériel — ' . $projectTitle
            : 'Financement — ' . $projectTitle;

        $lines = [
            'Projet : ' . $projectTitle,
            'Slug : ' . $projectSlug,
            'Type : ' . ($contributionType === 'material' ? 'Apport matériel' : 'Financement (budget)'),
            '',
        ];

        foreach ($fields as $label => $value) {
            if ($value === '' || in_array($label, ['donor_name', 'contact', 'email'], true)) {
                continue;
            }
            $lines[] = $label . ' : ' . $value;
        }

        if ($adminValidationUrl !== null && $adminValidationUrl !== '') {
            $lines[] = '';
            $lines[] = 'Valider dans le back-office :';
            $lines[] = $adminValidationUrl;
        }

        $body = implode("\n", $lines);

        try {
            $email = service('email');
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($recipientTo);
            if ($replyEmail !== '' && filter_var($replyEmail, FILTER_VALIDATE_EMAIL)) {
                $email->setReplyTo($replyEmail, $replyName !== '' ? $replyName : $replyEmail);
            }
            $email->setSubject($subject);
            $email->setMailType('text');
            $email->setMessage($body);

            if (! $email->send()) {
                log_message('error', 'ProjectContributionNotifier: échec — ' . $email->printDebugger(['headers']));
            }
        } catch (\Throwable $e) {
            log_message('error', 'ProjectContributionNotifier: ' . $e->getMessage());
        }
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
