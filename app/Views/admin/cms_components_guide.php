<?php

declare(strict_types=1);

/**
 * @var list<array{id:string,title:string,intro:string,html:string}> $sections
 */
?>
<div class="mb-4">
    <h1 class="h3 mb-2">Composants HTML du site public</h1>
    <p class="text-muted mb-0">
        Les blocs ci-dessous reprennent les classes du template (<span class="font-monospace small">section__*</span>, <span class="font-monospace small">cercle__*</span>, etc.). Sous chaque extrait : un aperçu isolé dans la charte sombre (sans publier de page dédiée).
    </p>
</div>

<?php foreach ($sections as $sec) : ?>
    <?php if ($sec['id'] === 'intro') : ?>
        <div class="alert alert-info mb-4">
            <strong><?= esc($sec['title']) ?>.</strong>
            <?= esc($sec['intro']) ?>
        </div>
    <?php elseif ($sec['html'] !== '') : ?>
        <?php
        $canvas = match ($sec['id']) {
            'wire-full-section' => null,
            'home-program' => null,
            'section-header',
            'cercles' => 'section section--qui',
            'adn' => 'section section--adn',
            'structure' => 'section section--structure',
            'secteurs' => 'section section--secteurs',
            'etude' => 'section section--etude',
            'contact' => 'section section--contact',
            default => 'section section--qui',
        };
        ?>
        <div class="card mb-4" id="admin-<?= esc($sec['id'], 'attr') ?>">
            <div class="card-body">
                <h2 class="h5 card-title"><?= esc($sec['title']) ?></h2>
                <p class="card-text small text-muted"><?= esc($sec['intro']) ?></p>

                <label class="form-label small fw-semibold mb-1">HTML exemple (copier dans l’éditeur)</label>
                <textarea class="form-control font-monospace small mb-3" rows="12" readonly spellcheck="false"><?= esc($sec['html']) ?></textarea>

                <label class="form-label small fw-semibold mb-1 text-muted">Aperçu charte</label>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Rendu</div>
                    <?php if ($canvas === null) : ?>
                        <div class="cms-guide-sample__canvas cms-guide-sample__canvas--flush">
                            <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell">
                                <?= $sec['html'] ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="cms-guide-sample__canvas">
                            <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell">
                                <article class="wysiwyg ggz-cms-fullwidth">
                                    <section class="<?= esc($canvas, 'attr') ?>">
                                        <div class="section__inner">
                                            <?= $sec['html'] ?>
                                        </div>
                                    </section>
                                </article>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
