<div class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
    <section class="section section--press" aria-labelledby="press-heading">
        <div class="section__inner">
            <nav class="ggz-breadcrumb" aria-label="<?= esc(lang('Site.breadcrumb_aria'), 'attr') ?>">
                <a href="<?= esc(localized_site_url(''), 'attr') ?>"><?= esc(lang('Site.breadcrumb_home')) ?></a>
                <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
                <span class="muted"><?= esc(lang('Site.breadcrumb_press')) ?></span>
            </nav>
            <div class="section__header">
                <div class="section__overline"><?= esc(lang('Site.press_overline')) ?></div>
                <h1 class="section__title" id="press-heading"><?= esc(lang('Site.breadcrumb_press')) ?></h1>
                <p class="section__lead">
                    <?= esc(lang('Site.press_index_intro')) ?>
                </p>
            </div>
            <?php if ($posts === []) : ?>
                <div class="ggz-empty-state">
                    <p><?= esc(lang('Site.press_empty_none')) ?></p>
                    <p class="muted">
                        <a href="<?= esc(localized_site_url('contact'), 'attr') ?>"><?= esc(lang('Site.press_media_contact')) ?></a>
                    </p>
                </div>
            <?php else : ?>
                <div class="ggz-page-press-index">
                    <ul class="ggz-press-grid">
                        <?php foreach ($posts as $post) : ?>
                            <?php
                            $pub = cms_format_publish_date($post['published_at'] ?? null);
                            ?>
                            <li>
                                <article class="ggz-press-card">
                                    <?php if ($pub !== '') : ?>
                                        <time class="ggz-press-card__date" datetime="<?= esc((string) ($post['published_at'] ?? ''), 'attr') ?>"><?= esc($pub) ?></time>
                                    <?php endif; ?>
                                    <h2 class="cercle__title">
                                        <a href="<?= esc(localized_site_url('press/' . $post['slug']), 'attr') ?>"><?= esc($post['title']) ?></a>
                                    </h2>
                                    <?php if (! empty($post['excerpt'])) : ?>
                                        <p class="ggz-press-card__excerpt"><?= esc($post['excerpt']) ?></p>
                                    <?php endif; ?>
                                    <a class="btn btn--primary" href="<?= esc(localized_site_url('press/' . $post['slug']), 'attr') ?>"><?= esc(lang('Site.press_read_release')) ?></a>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
