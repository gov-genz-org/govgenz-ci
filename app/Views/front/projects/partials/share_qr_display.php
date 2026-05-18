<?php

declare(strict_types=1);

/** @var string $qrImageUrl */
/** @var string $qrAlt */
/** @var string $frameClass Extra classes on frame (e.g. --large) */
/** @var int $imgWidth */
/** @var int $imgHeight */
/** @var bool $overlayLogo Affiche le logo centré (secours visuel + partage social) */

$qrImageUrl   = (string) ($qrImageUrl ?? '');
$qrAlt        = (string) ($qrAlt ?? '');
$frameClass   = trim((string) ($frameClass ?? ''));
$imgWidth     = (int) ($imgWidth ?? 140);
$imgHeight    = (int) ($imgHeight ?? 140);
$overlayLogo  = (bool) ($overlayLogo ?? true);

$frameClasses = 'projects-program-show__share-qr-frame';
if ($frameClass !== '') {
    $frameClasses .= ' ' . $frameClass;
}

$logoQrUrl = base_url('assets/img/govgenz-logo-qr.png');
?>
<div class="<?= esc($frameClasses, 'attr') ?>">
    <img
        class="projects-program-show__share-qr"
        src="<?= esc($qrImageUrl, 'attr') ?>"
        width="<?= $imgWidth ?>"
        height="<?= $imgHeight ?>"
        alt="<?= esc($qrAlt, 'attr') ?>"
        <?php if (! empty($qrDataAttr)) : ?>data-project-share-qr<?php endif; ?>
        decoding="async"
    >
    <?php if ($overlayLogo) : ?>
        <img
            class="projects-program-show__share-qr-logo"
            src="<?= esc($logoQrUrl, 'attr') ?>"
            width="32"
            height="32"
            alt=""
            aria-hidden="true"
            decoding="async"
        >
    <?php endif; ?>
</div>
