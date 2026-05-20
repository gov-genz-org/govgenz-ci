<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Accusé de réception envoyé au visiteur après Rejoindre ou Financer un projet.
 */
final class FormSubmissionAckMailer
{
    /**
     * @param list<array{label: string, value: string}> $summaryLines
     */
    public static function sendJoin(string $toEmail, string $fullName, array $summaryLines, string $locale): void
    {
        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if ($locale !== 'en') {
            $locale = 'fr';
        }

        helper(['language', 'email']);

        $siteName = self::siteName();
        $subject  = lang('Site.join_ack_subject', [], $locale);
        $viewData = [
            'locale'       => $locale,
            'logoUrl'      => email_brand_logo_url(),
            'siteName'     => $siteName,
            'headline'     => lang('Site.join_ack_headline', [], $locale),
            'intro'        => lang('Site.join_ack_intro', [], $locale),
            'greetingName' => $fullName,
            'summaryTitle' => lang('Site.join_ack_summary_title', [], $locale),
            'summaryLines' => $summaryLines,
            'footerNote'   => lang('Site.join_ack_footer', [], $locale),
        ];

        $plain = self::buildPlainJoin($fullName, $summaryLines, $locale, $siteName);
        self::deliver($toEmail, $subject, $viewData, $plain);
    }

    /**
     * @param list<array{label: string, value: string}> $summaryLines
     */
    public static function sendProjectFund(
        string $toEmail,
        string $donorName,
        string $projectTitle,
        string $contributionType,
        array $summaryLines,
        string $locale,
    ): void {
        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if ($locale !== 'en') {
            $locale = 'fr';
        }

        helper(['language', 'email']);

        $siteName = self::siteName();
        $typeKey  = $contributionType === 'material' ? 'material' : 'budget';
        $subject  = lang('Projects.fund_ack_subject', ['title' => $projectTitle], $locale);
        $viewData = [
            'locale'       => $locale,
            'logoUrl'      => email_brand_logo_url(),
            'siteName'     => $siteName,
            'headline'     => lang('Projects.fund_ack_headline', [], $locale),
            'intro'        => lang('Projects.fund_ack_intro', ['title' => $projectTitle], $locale),
            'greetingName' => $donorName,
            'summaryTitle' => lang('Projects.fund_ack_summary_title', [], $locale),
            'summaryLines' => array_merge([
                [
                    'label' => lang('Projects.fund_ack_type_label', [], $locale),
                    'value' => lang('Projects.fund_ack_type_' . $typeKey, [], $locale),
                ],
            ], $summaryLines),
            'footerNote'   => lang('Projects.fund_ack_footer', [], $locale),
        ];

        $plain = self::buildPlainFund($donorName, $projectTitle, $contributionType, $summaryLines, $locale, $siteName);
        self::deliver($toEmail, $subject, $viewData, $plain);
    }

    /**
     * @param array<string, mixed> $viewData
     */
    private static function deliver(string $toEmail, string $subject, array $viewData, string $plain): void
    {
        $config    = config('Email');
        $fromEmail = self::resolveFromEmail(trim($config->fromEmail));
        $fromName  = self::siteName();

        if ($fromEmail === '') {
            log_message('warning', 'FormSubmissionAckMailer: expéditeur manquant — accusé ignoré.');

            return;
        }

        $html = view('emails/form_submission_ack', $viewData);

        try {
            $email = service('email');
            $email->clear(true);
            $email->setFrom($fromEmail, $fromName);
            $email->setTo($toEmail);
            $email->setSubject($subject);
            $email->setMailType('html');
            $email->setMessage($html);
            $email->setAltMessage($plain);

            if (! $email->send()) {
                log_message('error', 'FormSubmissionAckMailer: échec — ' . $email->printDebugger(['headers']));
            }
        } catch (\Throwable $e) {
            log_message('error', 'FormSubmissionAckMailer: ' . $e->getMessage());
        }
    }

    /**
     * @param list<array{label: string, value: string}> $summaryLines
     */
    private static function buildPlainJoin(string $fullName, array $summaryLines, string $locale, string $siteName): string
    {
        $lines = [
            lang('Site.join_ack_greeting', ['name' => $fullName], $locale),
            '',
            lang('Site.join_ack_intro', [], $locale),
            '',
            lang('Site.join_ack_summary_title', [], $locale) . ':',
        ];
        foreach ($summaryLines as $row) {
            $val = trim($row['value']);
            if ($val === '') {
                continue;
            }
            $lines[] = $row['label'] . ': ' . $val;
        }
        $lines[] = '';
        $lines[] = lang('Site.join_ack_footer', [], $locale);
        $lines[] = '';
        $lines[] = '— ' . $siteName;

        return implode("\n", $lines);
    }

    /**
     * @param list<array{label: string, value: string}> $summaryLines
     */
    private static function buildPlainFund(
        string $donorName,
        string $projectTitle,
        string $contributionType,
        array $summaryLines,
        string $locale,
        string $siteName,
    ): string {
        $typeKey = $contributionType === 'material' ? 'material' : 'budget';
        $lines   = [
            lang('Projects.fund_ack_greeting', ['name' => $donorName], $locale),
            '',
            lang('Projects.fund_ack_intro', ['title' => $projectTitle], $locale),
            '',
            lang('Projects.fund_ack_summary_title', [], $locale) . ':',
            lang('Projects.fund_ack_type_label', [], $locale) . ': ' . lang('Projects.fund_ack_type_' . $typeKey, [], $locale),
        ];
        foreach ($summaryLines as $row) {
            $val = trim($row['value']);
            if ($val === '') {
                continue;
            }
            $lines[] = $row['label'] . ': ' . $val;
        }
        $lines[] = '';
        $lines[] = lang('Projects.fund_ack_footer', [], $locale);
        $lines[] = '';
        $lines[] = '— ' . $siteName;

        return implode("\n", $lines);
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
