<?php

declare(strict_types=1);

$pubRaw = (string) ($post['published_at'] ?? '');
$pub    = cms_format_publish_date($pubRaw !== '' ? $pubRaw : null);
?>
<div class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
    <section class="section section--press">
        <div class="section__inner">
            <nav class="ggz-breadcrumb" aria-label="Fil d’Ariane">
                <a href="<?= site_url('press') ?>">Presse</a>
                <span class="ggz-breadcrumb__sep" aria-hidden="true">/</span>
                <span class="muted">Communiqué</span>
            </nav>

            <div class="section__header">
                <div class="section__overline">COMMUNIQUÉ</div>
                <h1 class="section__title"><?= esc($post['title']) ?></h1>
                <?php if (! empty($post['excerpt'])) : ?>
                    <p class="section__lead"><?= esc($post['excerpt']) ?></p>
                <?php endif; ?>
                <?php if ($pub !== '') : ?>
                    <p class="section__meta">
                        <time datetime="<?= esc($pubRaw, 'attr') ?>">Publié le <?= esc($pub) ?></time>
                    </p>
                <?php endif; ?>
            </div>

            <div class="wysiwyg ggz-page-prose ggz-page-press-body"><?= $post['body_html'] ?></div>

            <p class="ggz-back-row">
                <a href="<?= site_url('press') ?>" class="btn secondary ggz-btn-back">← Retour aux communiqués</a>
            </p>
        </div>
    </section>
</div>
