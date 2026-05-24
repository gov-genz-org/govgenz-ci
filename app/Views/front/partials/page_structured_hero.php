<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $page
 */
helper(['cms']);

$overline = trim((string) ($page['hero_overline'] ?? ''));
$heroTitle = trim((string) ($page['hero_title'] ?? ''));
$displayTitle = $heroTitle;
$lead = trim((string) ($page['hero_lead'] ?? ''));
$slug = trim((string) ($page['slug'] ?? ''));
$headingId = $slug !== '' ? $slug . '-heading' : null;

$rawImgId = $page['hero_image_id'] ?? null;
$imgId    = ($rawImgId !== null && $rawImgId !== '') ? (int) $rawImgId : 0;
$imgUrl   = $imgId > 0 ? cms_media_public_url($imgId) : null;

$alt = trim((string) ($page['hero_image_alt'] ?? ''));
if ($alt === '' && $displayTitle !== '') {
    $alt = $displayTitle;
}
if ($alt === '') {
    $alt = '';
}

$heroClasses = 'ggz-page-hero ggz-page-hero--structured';
if ($imgUrl !== null) {
    $heroClasses .= ' ggz-page-hero--has-media';
}
?>
<header class="<?= esc($heroClasses, 'attr') ?>">
    <div class="ggz-page-hero__inner">
        <?php if ($imgUrl !== null) : ?>
            <figure class="ggz-page-hero__figure">
                <img src="<?= esc($imgUrl, 'attr') ?>" alt="<?= esc($alt, 'attr') ?>" class="ggz-page-hero__img" loading="lazy" decoding="async" width="640" height="360">
            </figure>
        <?php endif; ?>
        <div class="ggz-page-hero__copy section__header">
            <?php if ($overline !== '') : ?>
                <div class="section__overline"><?= esc($overline) ?></div>
            <?php endif; ?>
            <?php if ($displayTitle !== '') : ?>
                <h1<?= $headingId !== null ? ' id="' . esc($headingId, 'attr') . '"' : '' ?> class="section__title"><?= esc($displayTitle) ?></h1>
            <?php endif; ?>
            <?php if ($lead !== '') : ?>
                <p class="section__lead"><?= nl2br(esc($lead)) ?></p>
            <?php endif; ?>
        </div>
    </div>
</header>
