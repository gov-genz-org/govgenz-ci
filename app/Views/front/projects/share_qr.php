<?php

declare(strict_types=1);

helper(['language', 'project']);

/** @var array<string, mixed> $project */
/** @var string $title */
/** @var string $qrImageUrl */
/** @var string $projectUrl */
/** @var string $projectHref */

$title = (string) ($title ?? '');
?>
<article class="projects-program-show projects-program-show--share-qr" aria-labelledby="share-qr-heading">
    <div class="projects-program-show__share-qr-page">
        <h1 id="share-qr-heading" class="content-title teal"><?= esc(lang('Projects.share_qr_page_heading')) ?></h1>
        <?php if ($title !== '') : ?>
            <p class="projects-program-show__share-qr-page-lead"><?= esc($title) ?></p>
        <?php endif; ?>
        <?= view('front/projects/partials/share_qr_display', [
            'qrImageUrl'  => $qrImageUrl,
            'qrAlt'       => lang('Projects.show_share_qr_aria'),
            'frameClass'  => 'projects-program-show__share-qr-frame--large',
            'imgWidth'    => 280,
            'imgHeight'   => 280,
            'overlayLogo' => false,
        ]) ?>
        <p class="projects-program-show__share-qr-page-hint"><?= esc(lang('Projects.share_qr_page_hint')) ?></p>
        <p class="projects-program-show__share-qr-page-link">
            <a href="<?= esc($projectHref, 'attr') ?>"><?= esc(lang('Projects.share_qr_page_view_project')) ?></a>
        </p>
    </div>
</article>
