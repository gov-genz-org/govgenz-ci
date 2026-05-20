<?php

declare(strict_types=1);

/**
 * @var string $locale
 * @var string $logoUrl
 * @var string $siteName
 * @var string $overline
 * @var string $headline
 * @var string $intro
 * @var string $summaryTitle
 * @var list<array{label: string, value: string}> $summaryLines
 * @var string $ctaUrl
 * @var string $ctaLabel
 * @var string $footerNote
 */
$locale = ($locale ?? 'fr') === 'en' ? 'en' : 'fr';
$visibleSummary = array_values(array_filter(
    $summaryLines,
    static fn (array $row): bool => trim((string) ($row['value'] ?? '')) !== '',
));
?>
<!DOCTYPE html>
<html lang="<?= esc($locale, 'attr') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($headline) ?></title>
</head>
<body style="margin:0;padding:0;background-color:#e8ecf1;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#1a2332;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#e8ecf1;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:560px;">
                <tr>
                    <td align="center" style="padding-bottom:20px;">
                        <img src="<?= esc($logoUrl, 'attr') ?>" alt="<?= esc($siteName, 'attr') ?>" width="64" height="64" style="display:block;border:0;border-radius:8px;">
                    </td>
                </tr>
                <tr>
                    <td style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(10,22,40,0.08);">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td style="background:linear-gradient(135deg,#0a1628,#1e3a5f);padding:24px 32px;text-align:center;">
                                    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#fca5a5;"><?= esc($overline) ?></p>
                                    <h1 style="margin:0;font-size:21px;line-height:1.35;font-weight:700;color:#fff;"><?= esc($headline) ?></h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:24px 32px 12px;">
                                    <p style="margin:0;font-size:15px;line-height:1.65;color:#3d4f63;"><?= esc($intro) ?></p>
                                </td>
                            </tr>
                            <?php if ($visibleSummary !== []) : ?>
                            <tr>
                                <td style="padding:0 32px 20px;">
                                    <table role="presentation" width="100%" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                                        <tr>
                                            <td style="padding:16px 20px;">
                                                <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;color:#64748b;"><?= esc($summaryTitle) ?></p>
                                                <?php foreach ($visibleSummary as $row) : ?>
                                                <p style="margin:0 0 8px;font-size:14px;line-height:1.5;">
                                                    <strong><?= esc((string) $row['label']) ?>:</strong>
                                                    <?= esc((string) $row['value']) ?>
                                                </p>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if (trim((string) ($ctaUrl ?? '')) !== '') : ?>
                            <tr>
                                <td style="padding:0 32px 24px;text-align:center;">
                                    <a href="<?= esc($ctaUrl, 'attr') ?>" style="display:inline-block;padding:12px 24px;background:#0d9488;color:#fff;text-decoration:none;font-weight:600;font-size:14px;border-radius:8px;"><?= esc($ctaLabel) ?></a>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td style="padding:0 32px 24px;">
                                    <p style="margin:0;font-size:12px;line-height:1.55;color:#94a3b8;"><?= esc($footerNote) ?></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
