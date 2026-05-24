<?php

declare(strict_types=1);

/**
 * @var list<array{id:string,title:string,intro:string,html:string}> $sections
 */
?>
<div class="mb-4 admin-cms-guide-lead">
    <h1 class="h3 mb-2"><?= esc(lang('Admin.title_cms_components')) ?></h1>
    <p class="text-muted mb-0"><?= lang('Admin.cms_guide_lead') ?></p>
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
            'legal-mentions' => '__legal__',
            'site-footer' => '__footer__',
            'site-footer-minimal' => '__footer__',
            'section-header' => 'section section--qui',
            'cercles' => 'section section--qui',
            'adn' => 'section section--adn',
            'structure' => 'section section--structure',
            'secteurs' => 'section section--secteurs',
            'etude' => 'section section--etude',
            'contact' => 'section section--contact',
            'press-page' => 'section section--press',
            default => 'section section--qui',
        };
        ?>
        <div class="card mb-4" id="admin-<?= esc($sec['id'], 'attr') ?>">
            <div class="card-body">
                <h2 class="h5 card-title"><?= esc($sec['title']) ?></h2>
                <p class="card-text small text-muted"><?= esc($sec['intro']) ?></p>

                <label class="form-label small fw-semibold mb-1"><?= esc(lang('Admin.cms_guide_sample_html')) ?></label>
                <textarea class="form-control font-monospace small mb-3" rows="<?= $sec['id'] === 'legal-mentions' ? '22' : '12' ?>" readonly spellcheck="false"><?= esc($sec['html']) ?></textarea>

                <label class="form-label small fw-semibold mb-1 text-muted"><?= esc(lang('Admin.cms_guide_preview')) ?></label>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label"><?= esc(lang('Admin.cms_guide_render')) ?></div>
                    <?php if ($canvas === null) : ?>
                        <div class="cms-guide-sample__canvas cms-guide-sample__canvas--flush">
                            <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell">
                                <article class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
                                    <?= $sec['html'] ?>
                                </article>
                            </div>
                        </div>
                    <?php elseif ($canvas === '__legal__') : ?>
                        <?php helper('cms'); ?>
                        <div class="cms-guide-sample__canvas cms-guide-sample__canvas--flush">
                            <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell">
                                <?= cms_render_structured_page_hero(\App\Database\Support\CmsLegalMentionsBodies::guidePreviewPage()) ?>
                                <article class="wysiwyg ggz-cms-fullwidth ggz-cms-page--legal">
                                    <?= $sec['html'] ?>
                                </article>
                            </div>
                        </div>
                    <?php elseif ($canvas === '__footer__') : ?>
                        <div class="cms-guide-sample__canvas cms-guide-sample__canvas--footer">
                            <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell cms-guide-preview-host--footer">
                                <footer class="footer">
                                    <div class="footer__inner">
                                        <div class="footer__columns">
                                            <?= $sec['html'] ?>
                                        </div>
                                    </div>
                                </footer>
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
