<div class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
    <section class="section section--press" aria-labelledby="press-heading">
        <div class="section__inner">
            <div class="section__header">
                <div class="section__overline">MÉDIAS</div>
                <h1 class="section__title" id="press-heading">Presse</h1>
                <p class="section__lead">Communiqués et actualités publiés par GovGenZ Madagascar.    
                    <?php if ($posts === []) : ?>
                        <div class="ggz-empty-state">
                            <p>Aucun communiqué publié pour le moment.</p>
                            <br>
                            <p class="muted">Revenez plus tard ou utilisez le formulaire de <a class="cercle__sub" href="<?= site_url('contact') ?>">Contact</a> pour les demandes médias.</p>
                        </div>
                    <?php endif; ?>
                </p>
            </div>
            <div class="ggz-page-press-index">
                <?php if ($posts !== []) : ?>
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
                                    <h2 class="ggz-press-card__title">
                                        <a href="<?= site_url('press/' . $post['slug']) ?>"><?= esc($post['title']) ?></a>
                                    </h2>
                                    <?php if (! empty($post['excerpt'])) : ?>
                                        <p class="ggz-press-card__excerpt"><?= esc($post['excerpt']) ?></p>
                                    <?php endif; ?>
                                    <a class="ggz-press-card__cta" href="<?= site_url('press/' . $post['slug']) ?>">Lire le communiqué</a>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
