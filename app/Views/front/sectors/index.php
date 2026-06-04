<?php

declare(strict_types=1);

/**
 * Page Secteurs dédiée — grille dynamique depuis la table `sectors`.
 *
 * @var array<string, mixed> $page
 * @var list<array<string, mixed>> $sectors
 */

helper(['cms']);

$block        = cms_sectors_grid_block_from_page($page) ?? [];
$layout       = cms_sectors_normalize_layout($block['layout'] ?? null, 'wide');
$sectionClass = $layout === 'wide'
    ? 'section section--secteurs sectors-grid sectors-grid--wide'
    : 'section section--secteurs section--decl-teams sectors-grid sectors-grid--compact';

$headingId = trim((string) ($block['heading_id'] ?? ''));
if ($headingId === '') {
    $headingId = 'secteurs-heading';
}

$introHtml = '';
if (trim((string) ($block['title'] ?? '')) !== '' || trim((string) ($block['kicker'] ?? '')) !== '' || trim((string) ($block['lead'] ?? '')) !== '') {
    $introBlock = $block;
    if (trim((string) ($introBlock['heading_id'] ?? '')) === '') {
        $introBlock['heading_id'] = $headingId;
    }
    $introHtml = cms_sectors_render_block_intro_html($introBlock);
}
$bannerHtml = cms_sectors_render_block_banner_html($block, $layout);
?>
<section class="<?= esc($sectionClass, 'attr') ?>" id="secteurs-content" aria-labelledby="<?= esc($headingId, 'attr') ?>">
    <div class="section__inner">
        <?php if ($introHtml !== '') : ?>
            <?= $introHtml ?>
        <?php else : ?>
            <div class="section__header">
                <div class="section__overline"><?= esc(lang('Site.secteurs_overline')) ?></div>
                <h1 class="section__title" id="<?= esc($headingId, 'attr') ?>"><?= esc(lang('Site.secteurs_title')) ?></h1>
                <p class="section__lead"><?= esc(lang('Site.secteurs_lead')) ?></p>
            </div>
        <?php endif; ?>
        <?= $bannerHtml ?>
        <?= view('front/sectors/tile_grid', ['sectors' => $sectors, 'layout' => $layout]) ?>
    </div>
</section>
