<div class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth ggz-press-page ggz-press-page--index">
    <section class="section section--press" aria-labelledby="press-heading">
        <div class="section__inner ggz-press-layout">
            <header class="ggz-press-page__hero section__header">
                <div class="section__overline"><?= esc(lang('Site.press_overline')) ?></div>
                <h1 class="section__title" id="press-heading"><?= esc(lang('Site.breadcrumb_press')) ?></h1>
                <p class="section__lead"><?= esc(lang('Site.press_index_intro')) ?></p>
            </header>
            <?php if ($posts === []) : ?>
                <div class="ggz-press-empty cercle">
                    <p><?= esc(lang('Site.press_empty_none')) ?></p>
                    <p class="muted">
                        <a href="<?= esc(localized_site_url('contact'), 'attr') ?>"><?= esc(lang('Site.press_media_contact')) ?></a>
                    </p>
                </div>
            <?php else : ?>
                <div class="cercles ggz-press-cercles">
                    <?php foreach ($posts as $i => $post) :
                        $pub = cms_format_publish_date($post['published_at'] ?? null);
                        $url = localized_site_url('press/' . $post['slug']);
                        $ex  = trim((string) ($post['excerpt'] ?? ''));
                        $delay = (int) $i * 100;
                        ?>
                        <a class="cercle reveal ggz-press-cercle" href="<?= esc($url, 'attr') ?>" data-delay="<?= $delay ?>">
                            <div class="cercle__icon">
                                <?= view('front/press/partials/cercle_icon') ?>
                            </div>
                            <?php if ($pub !== '') : ?>
                                <p class="cercle__sub">
                                    <time datetime="<?= esc((string) ($post['published_at'] ?? ''), 'attr') ?>"><?= esc($pub) ?></time>
                                </p>
                            <?php endif; ?>
                            <h3 class="cercle__title"><?= esc($post['title']) ?></h3>
                            <?php if ($ex !== '') : ?>
                                <p class="cercle__desc"><?= esc($ex) ?></p>
                            <?php endif; ?>
                            <p class="cercle__desc ggz-press-cercle__cta"><?= esc(lang('Site.press_read_release')) ?> →</p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
