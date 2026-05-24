<?php

declare(strict_types=1);

$pubRaw = (string) ($post['published_at'] ?? '');
$pub    = cms_format_publish_date($pubRaw !== '' ? $pubRaw : null);
$title  = (string) ($post['title'] ?? '');
$excerpt = trim((string) ($post['excerpt'] ?? ''));
$pressListUrl = localized_site_url('press');
$headingId = 'press-show-heading';
?>
<nav class="ggz-breadcrumb ggz-press-detail__breadcrumb" aria-label="<?= esc(lang('Site.breadcrumb_aria'), 'attr') ?>">
    <a href="<?= esc(localized_site_url(''), 'attr') ?>"><?= esc(lang('Site.breadcrumb_home')) ?></a>
    <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
    <a href="<?= esc($pressListUrl, 'attr') ?>"><?= esc(lang('Site.breadcrumb_press')) ?></a>
    <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
    <span class="muted"><?= esc(lang('Site.breadcrumb_press_article')) ?></span>
</nav>

<header class="ggz-page-hero ggz-page-hero--structured">
    <div class="ggz-page-hero__inner">
        <div class="ggz-page-hero__copy section__header">
            <div class="section__overline"><?= esc(lang('Site.press_show_overline')) ?></div>
            <h1 id="<?= esc($headingId, 'attr') ?>" class="section__title"><?= esc($title) ?></h1>
            <?php if ($excerpt !== '') : ?>
                <p class="section__lead"><?= esc($excerpt) ?></p>
            <?php endif; ?>
            <?php if ($pub !== '') : ?>
                <p class="ggz-press-hero-meta">
                    <time datetime="<?= esc($pubRaw, 'attr') ?>"><?= esc(lang('Site.press_published_label')) ?> <?= esc($pub) ?></time>
                </p>
            <?php endif; ?>
        </div>
    </div>
</header>

<article class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth ggz-cms-page--press">
    <section class="section section--press" aria-labelledby="<?= esc($headingId, 'attr') ?>">
        <div class="section__inner">
            <div class="ggz-legal-prose">
                <?= $post['body_html'] ?>
            </div>

            <p class="ggz-press-back-row">
                <a href="<?= esc($pressListUrl, 'attr') ?>" class="btn btn--ghost"><?= esc(lang('Site.press_back_list')) ?></a>
            </p>
        </div>
    </section>
</article>
