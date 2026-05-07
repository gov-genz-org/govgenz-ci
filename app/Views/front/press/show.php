<?php

declare(strict_types=1);

$pubRaw = (string) ($post['published_at'] ?? '');
$pub    = cms_format_publish_date($pubRaw !== '' ? $pubRaw : null);
?>
<div class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
    <section class="section section--press">
        <div class="section__inner">
            <nav class="ggz-breadcrumb" aria-label="<?= esc(lang('Site.breadcrumb_aria'), 'attr') ?>">
                <a href="<?= esc(localized_site_url(''), 'attr') ?>"><?= esc(lang('Site.breadcrumb_home')) ?></a>
                <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
                <a href="<?= esc(localized_site_url('press'), 'attr') ?>"><?= esc(lang('Site.breadcrumb_press')) ?></a>
                <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
                <span class="muted"><?= esc(lang('Site.breadcrumb_press_article')) ?></span>
            </nav>

            <div class="section__header">
                <div class="section__overline"><?= esc(lang('Site.press_show_overline')) ?></div>
                <h1 class="section__title"><?= esc($post['title']) ?></h1>
                <?php if (! empty($post['excerpt'])) : ?>
                    <p class="section__lead"><?= esc($post['excerpt']) ?></p>
                <?php endif; ?>
                <?php if ($pub !== '') : ?>
                    <p class="section__meta">
                        <time datetime="<?= esc($pubRaw, 'attr') ?>"><?= esc(lang('Site.press_published_label')) ?> <?= esc($pub) ?></time>
                    </p>
                <?php endif; ?>
            </div>

            <div class="wysiwyg ggz-page-prose ggz-page-press-body"><?= $post['body_html'] ?></div>

            <p class="ggz-back-row">
                <a href="<?= esc(localized_site_url('press'), 'attr') ?>" class="btn btn--ghost ggz-btn-back"><?= esc(lang('Site.press_back_list')) ?></a>
            </p>
        </div>
    </section>
</div>
