<?php

declare(strict_types=1);

/**
 * @var string $roleLabel
 * @var string $setupUrl
 * @var string $expiryLabel
 * @var string $logoUrl
 * @var string $siteName
 */
$siteName = $siteName ?? 'GoV Gen Z Madagascar';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Invitation back-office</title>
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
                                    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#fca5a5;">Back-office</p>
                                    <h1 style="margin:0;font-size:22px;line-height:1.35;font-weight:700;color:#ffffff;">Activez votre compte</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:32px 32px 8px;">
                                    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#334155;">Bonjour,</p>
                                    <p style="margin:0 0 20px;font-size:15px;line-height:1.65;color:#475569;">
                                        Un compte <strong style="color:#1a2332;"><?= esc($roleLabel) ?></strong> a été créé pour vous sur le back-office
                                        <strong><?= esc($siteName) ?></strong>.
                                    </p>
                                    <p style="margin:0 0 28px;font-size:15px;line-height:1.65;color:#475569;">
                                        Cliquez sur le bouton ci-dessous pour confirmer votre adresse e-mail et choisir votre mot de passe.
                                    </p>
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td align="center" style="padding-bottom:8px;">
                                                <a href="<?= esc($setupUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"
                                                   style="display:inline-block;background-color:#dc2626;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:6px;box-shadow:0 2px 8px rgba(220,38,38,0.35);">
                                                    Choisir mon mot de passe
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    <p style="margin:24px 0 0;font-size:13px;line-height:1.5;color:#64748b;text-align:center;">
                                        Lien valable <strong><?= esc($expiryLabel) ?></strong>.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 32px 28px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                                        <tr>
                                            <td style="padding:14px 16px;">
                                                <p style="margin:0 0 6px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Lien direct</p>
                                                <p style="margin:0;font-size:12px;line-height:1.5;word-break:break-all;">
                                                    <a href="<?= esc($setupUrl, 'attr') ?>" style="color:#17a39e;text-decoration:underline;"><?= esc($setupUrl) ?></a>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 32px 28px;">
                                    <p style="margin:0;font-size:13px;line-height:1.55;color:#94a3b8;">
                                        Si vous n’êtes pas à l’origine de cette demande, ignorez ce message. Votre compte ne sera pas activé sans action de votre part.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:24px 16px 0;">
                        <p style="margin:0;font-size:12px;line-height:1.5;color:#94a3b8;">
                            <?= esc($siteName) ?> · Programme Paikady Taninjanaka
                        </p>
                        <p style="margin:8px 0 0;font-size:11px;color:#cbd5e1;">
                            <a href="<?= esc(email_absolute_url(), 'attr') ?>" style="color:#64748b;text-decoration:none;"><?= esc(parse_url(email_absolute_url(), PHP_URL_HOST) ?: 'genzgov.org') ?></a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
