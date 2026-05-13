<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $page
 * @var list<array<string, mixed>> $sectors
 */

helper(['cms']);

$bodyHtml = trim((string) cms_render_page_body($page));
?>
<section class="section section--secteurs" id="secteurs-content" aria-labelledby="secteurs-heading">
    <div class="section__inner">
        <div class="section__header">
            <div class="section__overline"><?= esc(lang('Site.secteurs_overline')) ?></div>
            <h1 class="section__title" id="secteurs-heading"><?= esc(lang('Site.secteurs_title')) ?></h1>
            <p class="section__lead"><?= esc(lang('Site.secteurs_lead')) ?></p>
        </div>
        <?php if ($bodyHtml !== '') : ?>
            <div class="wysiwyg ggz-sector-cms-body"><?= $bodyHtml ?></div>
        <?php else : ?>
            <?= view('front/sectors/tile_grid', ['sectors' => $sectors]) ?>
        <?php endif; ?>
    </div>
</section>
