<?php

declare(strict_types=1);

/** @var array<string, mixed> $item */
/** @var string $title */
/** @var string $qrImageUrl */
/** @var string $positionUrl */
/** @var string $positionHref */
?>
<article class="projects-program-show projects-program-show--share-qr" aria-labelledby="share-qr-heading">
    <div class="projects-program-show__share-qr-page">
        <h1 id="share-qr-heading" class="content-title teal"><?= esc(lang('Positions.share_qr_page_heading')) ?></h1>
        <?php if ($title !== '') : ?>
            <p class="projects-program-show__share-qr-page-lead"><?= esc($title) ?></p>
        <?php endif; ?>
        <?= view('front/projects/partials/share_qr_display', [
            'qrImageUrl'  => $qrImageUrl,
            'qrAlt'       => lang('Positions.show_share_qr_aria'),
            'frameClass'  => 'projects-program-show__share-qr-frame--large',
            'imgWidth'    => 220,
            'imgHeight'   => 220,
            'overlayLogo' => false,
        ]) ?>
        <p class="projects-program-show__share-qr-page-hint"><?= esc(lang('Positions.share_qr_page_hint')) ?></p>
        <p class="projects-program-show__share-qr-page-link">
            <a href="<?= esc($positionHref, 'attr') ?>"><?= esc(lang('Positions.show_share_qr_view_position')) ?></a>
        </p>
    </div>
</article>
