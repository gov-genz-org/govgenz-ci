<?php

declare(strict_types=1);

use App\Libraries\CmsBodyBlocksRenderer;

/** @var list<array{id:string,title:string,usage:string,blocks:list<array<string,mixed>>}> $examples */
$guideBlocksUrl = site_url('admin/cms-guide-blocks');
$footerAnchorUrl = $guideBlocksUrl . '#admin-block-footer_columns';
$componentsFooterUrl = site_url('admin/cms-guide') . '#admin-site-footer';
?>
<div class="admin-cms-blocks-guide">
<div class="mb-4 admin-cms-guide-lead">
    <h1 class="h3 mb-2"><?= esc(lang('Admin.title_cms_blocks_guide')) ?></h1>
    <p class="text-muted mb-0">
        Aperçu de chaque bloc du Page Builder et sommaire des types (<code>section_text</code>, <code>footer_columns</code>, etc.).
    </p>
</div>

<div class="card mb-4 border-blocks-guide-accent" id="admin-block-footer_columns-summary">
    <div class="card-body">
        <h2 class="h5 card-title mb-2">Pied de page du site</h2>
        <p class="small text-muted mb-2">
            Type de bloc : <code>footer_columns</code> — libellé dans l’éditeur : <strong>+ Colonnes pied de page</strong>.
            Page CMS : slug <code>site-footer</code> (publiée, FR et EN), mode <strong>Blocs</strong>.
        </p>
        <p class="small mb-3">
            HTML manuel (sans bloc) : voir aussi <a href="<?= esc($componentsFooterUrl, 'attr') ?>">Aide composants HTML → Pied de page</a>.
        </p>
        <a class="btn btn-sm btn-outline-secondary" href="<?= esc($footerAnchorUrl, 'attr') ?>">Voir l’exemple complet ci-dessous</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <h2 class="h6 card-title mb-2">Sommaire des blocs</h2>
        <ul class="small mb-0 row row-cols-1 row-cols-md-2 list-unstyled">
            <?php foreach ($examples as $example) : ?>
                <li class="mb-1 col">
                    <a href="#admin-block-<?= esc($example['id'], 'attr') ?>"><?= esc($example['title']) ?></a>
                    <span class="text-muted">(<code><?= esc($example['id']) ?></code>)</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php foreach ($examples as $example) : ?>
    <div class="card mb-4" id="admin-block-<?= esc($example['id'], 'attr') ?>">
        <div class="card-body">
            <h2 class="h5 card-title mb-1">
                <?= esc($example['title']) ?>
                <span class="text-muted fw-normal small">— type <code><?= esc($example['id']) ?></code></span>
            </h2>
            <p class="card-text small text-muted mb-3"><?= esc($example['usage']) ?></p>

            <label class="form-label small fw-semibold mb-1 text-muted">Exemple de donnees (JSON)</label>
            <textarea class="form-control font-monospace small mb-3" rows="8" readonly spellcheck="false"><?= esc((string) json_encode($example['blocks'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>

            <label class="form-label small fw-semibold mb-1 text-muted">Rendu</label>
            <?php if ($example['id'] === 'footer_columns') : ?>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Apercu pied de page (comme sur le site)</div>
                    <?= view('admin/partials/cms_guide_footer_canvas', [
                        'html' => CmsBodyBlocksRenderer::render($example['blocks']),
                    ]) ?>
                </div>
            <?php elseif ($example['id'] === 'sectors_grid') : ?>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Apercu grille secteurs (comme sur le site)</div>
                    <?= view('admin/partials/cms_guide_sectors_canvas', [
                        'html' => CmsBodyBlocksRenderer::render($example['blocks']),
                    ]) ?>
                </div>
            <?php else : ?>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Apercu bloc</div>
                    <div class="cms-guide-sample__canvas cms-guide-sample__canvas--flush">
                        <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell">
                            <article class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
                                <?= CmsBodyBlocksRenderer::render($example['blocks']) ?>
                            </article>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
