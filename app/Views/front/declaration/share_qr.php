<?php

declare(strict_types=1);

/** @var array<string, mixed> $item */
/** @var string $title */
/** @var string $qrImageUrl */
/** @var string $declarationUrl */
/** @var string $declarationHref */
?>
<article class="projects-program-show projects-program-show--share-qr" aria-labelledby="share-qr-heading">
    <div class="projects-program-show__share-qr-page">
        <h1 id="share-qr-heading" class="content-title teal"><?= esc(lang('Declaration.share_qr_page_heading')) ?></h1>
        <?php if ($title !== '') : ?>
            <p class="projects-program-show__share-qr-page-lead"><?= esc($title) ?></p>
        <?php endif; ?>
        <?= view('front/projects/partials/share_qr_display', [
            'qrImageUrl'  => $qrImageUrl,
            'qrAlt'       => lang('Declaration.show_share_qr_aria'),
            'frameClass'  => 'projects-program-show__share-qr-frame--large',
            'imgWidth'    => 220,
            'imgHeight'   => 220,
            'overlayLogo' => false,
        ]) ?>
        <p class="projects-program-show__share-qr-page-hint"><?= esc(lang('Declaration.share_qr_page_hint')) ?></p>
        <p class="projects-program-show__share-qr-page-link">
            <a href="<?= esc($declarationHref, 'attr') ?>"><?= esc(lang('Declaration.show_share_qr_view_declaration')) ?></a>
        </p>
    </div>
</article>
