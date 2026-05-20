<?php

declare(strict_types=1);

/**
 * @var string $locale
 * @var string $logoUrl
 * @var string $siteName
 * @var string $headline
 * @var string $intro
 * @var string $greetingName
 * @var string $summaryTitle
 * @var list<array{label: string, value: string}> $summaryLines
 * @var string $footerNote
 */
$locale = ($locale ?? 'fr') === 'en' ? 'en' : 'fr';
$htmlLang = $locale;
?>
<!DOCTYPE html>
<html lang="<?= esc($htmlLang, 'attr') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= esc($headline) ?></title>
</head>
<body style="margin:0;padding:0;background-color:#e8ecf1;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#1a2332;-webkit-font-smoothing:antialiased;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#e8ecf1;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:560px;width:100%;">
                <tr>
                    <td align="center" style="padding-bottom:20px;">
                        <img src="<?= esc($logoUrl, 'attr') ?>" alt="<?= esc($siteName, 'attr') ?>" width="64" height="64" style="display:block;border:0;outline:none;text-decoration:none;border-radius:8px;">
                    </td>
                </tr>
                <tr>
                    <td style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(10,22,40,0.08);">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td style="background:linear-gradient(135deg,#0a1628 0%,#121d30 100%);padding:28px 32px 24px;text-align:center;">
                                    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#7dd3c0;">Gov Gen Z</p>
                                    <h1 style="margin:0;font-size:22px;line-height:1.35;font-weight:700;color:#ffffff;"><?= esc($headline) ?></h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:28px 32px 8px;">
                                    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#1a2332;">
                                        <?= esc($locale === 'en' ? 'Hello' : 'Bonjour') ?> <?= esc($greetingName) ?>,
                                    </p>
                                    <p style="margin:0 0 20px;font-size:15px;line-height:1.65;color:#3d4f63;"><?= esc($intro) ?></p>
                                </td>
                            </tr>
                            <?php
                            $visibleSummary = array_values(array_filter(
                                $summaryLines,
                                static fn (array $row): bool => trim((string) ($row['value'] ?? '')) !== '',
                            ));
                            ?>
                            <?php if ($visibleSummary !== []) : ?>
                            <tr>
                                <td style="padding:0 32px 28px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f7fa;border-radius:8px;border:1px solid #e2e8f0;">
                                        <tr>
                                            <td style="padding:16px 20px;">
                                                <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#64748b;"><?= esc($summaryTitle) ?></p>
                                                <?php foreach ($visibleSummary as $row) : ?>
                                                <p style="margin:0 0 8px;font-size:14px;line-height:1.5;color:#1a2332;">
                                                    <strong style="color:#0a1628;"><?= esc((string) $row['label']) ?>:</strong>
                                                    <?= esc((string) $row['value']) ?>
                                                </p>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding:0 32px 28px;">
                                    <p style="margin:0;font-size:13px;line-height:1.55;color:#64748b;"><?= esc($footerNote) ?></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top:16px;">
                        <p style="margin:0;font-size:12px;color:#94a3b8;"><?= esc($siteName) ?></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
