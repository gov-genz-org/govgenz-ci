<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * E-mails HTML vers l’équipe (alertes back-office des formulaires publics).
 * Un envoi par modérateur (évite les BCC souvent ignorés par mail()).
 */
final class FormModeratorMailer
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
    public static function sendJoinAlert(array $payload, string $locale): void
    {
        if ($locale !== 'en') {
            $locale = 'fr';
        }

        helper(['language', 'email']);

        $sectorRaw   = (string) ($payload['sector_label'] ?? '');
        $sectorLines = array_values(array_filter(
            array_map('trim', explode("\n", $sectorRaw)),
            static fn (string $s): bool => $s !== '',
        ));
        $summaryLines = [
            ['label' => lang('Site.join_sector_label', [], $locale), 'value' => $sectorLines !== [] ? implode(', ', $sectorLines) : '—'],
            ['label' => lang('Site.join_label_full_name', [], $locale), 'value' => (string) $payload['full_name']],
            ['label' => lang('Site.join_label_email', [], $locale), 'value' => (string) $payload['email']],
            ['label' => lang('Site.join_label_phone', [], $locale), 'value' => (string) ($payload['phone'] ?? '') !== '' ? (string) $payload['phone'] : '—'],
            ['label' => lang('Site.join_label_message', [], $locale), 'value' => trim((string) ($payload['message'] ?? '')) !== '' ? (string) $payload['message'] : '—'],
            ['label' => 'ID', 'value' => (string) ($payload['id'] ?? '')],
            ['label' => 'IP', 'value' => (string) ($payload['ip_address'] ?? '')],
        ];

        $viewData = self::alertViewData(
            $locale,
            lang('Site.moderator_join_alert_headline', [], $locale),
            lang('Site.moderator_join_alert_intro', [], $locale),
            lang('Site.moderator_alert_summary_title', [], $locale),
            $summaryLines,
            (string) $payload['admin_validation_url'],
        );

        $plain = self::buildPlainAlert(
            lang('Site.moderator_join_alert_headline', [], $locale),
            lang('Site.moderator_join_alert_intro', [], $locale),
            $summaryLines,
            (string) $payload['admin_validation_url'],
            $locale,
        );

        self::broadcastToModerators(
            lang('Site.join_notify_subject', [], $locale),
            $viewData,
            $plain,
            (string) $payload['email'],
            (string) $payload['full_name'],
        );
    }

    /**
     * @param array<string, string> $fields
     */
    public static function sendFundAlert(
        string $contributionType,
        string $projectTitle,
        string $projectSlug,
        array $fields,
        string $adminValidationUrl,
        string $locale,
    ): void {
        if ($locale !== 'en') {
            $locale = 'fr';
        }

        helper(['language', 'email']);

        $typeKey = $contributionType === 'material' ? 'material' : 'budget';
        $summaryLines = [
            ['label' => lang('Projects.moderator_alert_project_label', [], $locale), 'value' => $projectTitle],
            ['label' => 'Slug', 'value' => $projectSlug],
            [
                'label' => lang('Projects.fund_ack_type_label', [], $locale),
                'value' => lang('Projects.fund_ack_type_' . $typeKey, [], $locale),
            ],
        ];

        $donorName  = trim($fields['donor_name'] ?? '');
        $donorPhone = trim($fields['contact'] ?? '');
        $donorMail  = trim($fields['donor_email'] ?? '');
        if ($donorName !== '') {
            $summaryLines[] = ['label' => lang('Projects.fund_field_name', [], $locale), 'value' => $donorName];
        }
        if ($donorPhone !== '') {
            $summaryLines[] = ['label' => lang('Projects.fund_field_phone', [], $locale), 'value' => $donorPhone];
        }
        if ($donorMail !== '') {
            $summaryLines[] = ['label' => lang('Projects.fund_field_email', [], $locale), 'value' => $donorMail];
        }

        foreach ($fields as $label => $value) {
            if ($value === '' || in_array($label, ['donor_name', 'contact', 'donor_email', 'email'], true)) {
                continue;
            }
            $summaryLines[] = ['label' => $label, 'value' => $value];
        }

        $viewData = self::alertViewData(
            $locale,
            lang('Projects.moderator_fund_alert_headline', ['title' => $projectTitle], $locale),
            lang('Projects.moderator_fund_alert_intro', ['title' => $projectTitle], $locale),
            lang('Projects.moderator_alert_summary_title', [], $locale),
            $summaryLines,
            $adminValidationUrl,
        );

        $plain = self::buildPlainAlert(
            lang('Projects.moderator_fund_alert_headline', ['title' => $projectTitle], $locale),
            lang('Projects.moderator_fund_alert_intro', ['title' => $projectTitle], $locale),
            $summaryLines,
            $adminValidationUrl,
            $locale,
        );

        $subject = lang('Projects.moderator_fund_alert_subject', ['title' => $projectTitle], $locale);
        $replyTo = $donorMail !== '' && filter_var($donorMail, FILTER_VALIDATE_EMAIL) ? $donorMail : '';
        $replyName = $donorName !== '' ? $donorName : $replyTo;

        self::broadcastToModerators($subject, $viewData, $plain, $replyTo, $replyName);
    }

    /**
     * @param list<array{label: string, value: string}> $summaryLines
     *
     * @return array<string, mixed>
     */
    private static function alertViewData(
        string $locale,
        string $headline,
        string $intro,
        string $summaryTitle,
        array $summaryLines,
        string $adminUrl,
    ): array {
        return [
            'locale'       => $locale,
            'logoUrl'      => email_brand_logo_url(),
            'siteName'     => self::siteName(),
            'overline'     => lang('Site.moderator_overline', [], $locale),
            'headline'     => $headline,
            'intro'        => $intro,
            'summaryTitle' => $summaryTitle,
            'summaryLines' => $summaryLines,
            'ctaUrl'       => $adminUrl,
            'ctaLabel'     => lang('Site.moderator_alert_cta', [], $locale),
            'footerNote'   => lang('Site.moderator_alert_footer', [], $locale),
        ];
    }

    /**
     * @param list<array{label: string, value: string}> $summaryLines
     */
    private static function buildPlainAlert(
        string $headline,
        string $intro,
        array $summaryLines,
        string $adminUrl,
        string $locale,
    ): string {
        $lines = [$headline, '', $intro, ''];
        foreach ($summaryLines as $row) {
            $val = trim($row['value']);
            if ($val === '') {
                continue;
            }
            $lines[] = $row['label'] . ': ' . $val;
        }
        $lines[] = '';
        $lines[] = lang('Site.moderator_alert_cta', [], $locale) . ':';
        $lines[] = $adminUrl;
        $lines[] = '';
        $lines[] = lang('Site.moderator_alert_footer', [], $locale);

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $viewData
     */
    private static function broadcastToModerators(
        string $subject,
        array $viewData,
        string $plain,
        string $replyToEmail = '',
        string $replyToName = '',
    ): void {
        $fromEmail = self::resolveFromEmail(trim((string) config('Email')->fromEmail));
        $fromName  = self::siteName();

        if ($fromEmail === '') {
            log_message('warning', 'FormModeratorMailer: expéditeur manquant.');

            return;
        }

        helper('email');
        $recipients = email_moderator_notification_addresses();
        if ($recipients === []) {
            log_message('warning', 'FormModeratorMailer: aucun modérateur actif.');

            return;
        }

        $html = view('emails/form_moderator_alert', $viewData);

        foreach ($recipients as $to) {
            try {
                $email = service('email');
                $email->clear(true);
                $email->setFrom($fromEmail, $fromName);
                $email->setTo($to);
                if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                    $email->setReplyTo($replyToEmail, $replyToName !== '' ? $replyToName : $replyToEmail);
                }
                $email->setSubject($subject);
                $email->setMailType('html');
                $email->setMessage($html);
                $email->setAltMessage($plain);

                if (! $email->send()) {
                    log_message('error', 'FormModeratorMailer: échec vers {to} — {dbg}', [
                        'to'  => $to,
                        'dbg' => $email->printDebugger(['headers']),
                    ]);
                }
            } catch (\Throwable $e) {
                log_message('error', 'FormModeratorMailer [{to}]: {msg}', ['to' => $to, 'msg' => $e->getMessage()]);
            }
        }
    }

    private static function siteName(): string
    {
        $config = config('Email');

        return trim($config->fromName) !== '' ? trim($config->fromName) : 'Gov Gen Z';
    }

    private static function resolveFromEmail(string $configured): string
    {
        if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_EMAIL)) {
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
