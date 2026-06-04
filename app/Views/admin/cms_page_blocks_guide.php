<?php

declare(strict_types=1);

use App\Libraries\CmsBodyBlocksRenderer;

/** @var list<array{id:string,title:string,usage:string,blocks:list<array<string,mixed>>}> $examples */
/** @var string $declarationItemsUrl */
/** @var string $pagesIndexUrl */
/** @var string $structuresAdminUrl */
/** @var string $sectorsAdminUrl */
/** @var string $componentsGuideUrl */
$guideBlocksUrl = site_url('admin/cms-guide-blocks');
$footerAnchorUrl = $guideBlocksUrl . '#admin-block-footer_columns';
$componentsFooterUrl = site_url('admin/cms-guide') . '#admin-site-footer';
?>
<div class="admin-cms-blocks-guide">
<div class="mb-4 admin-cms-guide-lead">
    <h1 class="h3 mb-2"><?= esc(lang('Admin.title_cms_blocks_guide')) ?></h1>
    <p class="text-muted mb-0"><?= lang('Admin.cms_blocks_guide_lead') ?></p>
</div>

<div class="card mb-4 border-primary" id="admin-guide-declaration-program">
    <div class="card-body">
        <h2 class="h5 card-title mb-2"><?= esc(lang('Admin.cms_blocks_guide_declaration_title')) ?></h2>
        <p class="small text-muted mb-3"><?= lang('Admin.cms_blocks_guide_declaration_intro') ?></p>
        <?= view('admin/pages/partials/declaration_blocks_order_guide') ?>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-sm btn-outline-primary" href="<?= esc($pagesIndexUrl, 'attr') ?>"><?= esc(lang('Admin.cms_blocks_guide_declaration_pages_link')) ?></a>
            <a class="btn btn-sm btn-outline-primary" href="<?= esc($declarationItemsUrl, 'attr') ?>"><?= esc(lang('Admin.cms_blocks_guide_declaration_items_link')) ?></a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= esc($structuresAdminUrl, 'attr') ?>"><?= esc(lang('Admin.cms_blocks_guide_declaration_structures_link')) ?></a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= esc($sectorsAdminUrl, 'attr') ?>"><?= esc(lang('Admin.cms_blocks_guide_declaration_sectors_link')) ?></a>
            <a class="btn btn-sm btn-link" href="#admin-block-structures_grid"><?= esc(lang('Admin.cms_block_type_structures_grid')) ?></a>
            <a class="btn btn-sm btn-link" href="#admin-block-sectors_grid"><?= esc(lang('Admin.cms_block_type_sectors_grid')) ?></a>
            <a class="btn btn-sm btn-link" href="#admin-block-legal_prose_accordion"><?= esc(lang('Admin.cms_block_type_legal_prose')) ?> (accordéon)</a>
        </div>
    </div>
</div>

<div class="card mb-4 border-secondary" id="admin-guide-structure-page">
    <div class="card-body">
        <h2 class="h5 card-title mb-2"><?= esc(lang('Admin.cms_blocks_guide_structure_title')) ?></h2>
        <p class="small text-muted mb-3"><?= lang('Admin.cms_blocks_guide_structure_intro') ?></p>
        <?= view('admin/pages/partials/structure_page_blocks_guide') ?>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <a class="btn btn-sm btn-outline-primary" href="<?= esc($pagesIndexUrl, 'attr') ?>"><?= esc(lang('Admin.cms_blocks_guide_structure_pages_link')) ?></a>
            <a class="btn btn-sm btn-outline-primary" href="<?= esc($structuresAdminUrl, 'attr') ?>"><?= esc(lang('Admin.cms_blocks_guide_declaration_structures_link')) ?></a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= esc($componentsGuideUrl, 'attr') ?>#admin-structures-dynamic"><?= esc(lang('Admin.cms_blocks_guide_structure_html_link')) ?></a>
            <a class="btn btn-sm btn-link" href="#admin-block-structures_grid_hub"><?= esc(lang('Admin.cms_structures_layout_hub')) ?></a>
        </div>
    </div>
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
                <span class="text-muted fw-normal small">— type <code><?= esc($example['blocks'][0]['type'] ?? $example['id']) ?></code></span>
            </h2>
            <p class="card-text small text-muted mb-3"><?= esc($example['usage']) ?></p>

            <label class="form-label small fw-semibold mb-1 text-muted">Exemple de données (JSON)</label>
            <textarea class="form-control font-monospace small mb-3" rows="8" readonly spellcheck="false"><?= esc((string) json_encode($example['blocks'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>

            <label class="form-label small fw-semibold mb-1 text-muted">Rendu</label>
            <?php if ($example['id'] === 'footer_columns') : ?>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Aperçu pied de page (comme sur le site)</div>
                    <?= view('admin/partials/cms_guide_footer_canvas', [
                        'html' => CmsBodyBlocksRenderer::render($example['blocks']),
                    ]) ?>
                </div>
            <?php elseif (in_array($example['id'], ['sectors_grid', 'sectors_grid_wide'], true)) : ?>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Aperçu grille secteurs (données BDD)</div>
                    <?= view('admin/partials/cms_guide_sectors_canvas', [
                        'html' => CmsBodyBlocksRenderer::render($example['blocks']),
                    ]) ?>
                </div>
            <?php elseif (in_array($example['id'], ['structures_grid', 'structures_grid_hub'], true)) : ?>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Aperçu grille structures (données BDD)</div>
                    <?= view('admin/partials/cms_guide_structures_canvas', [
                        'html' => CmsBodyBlocksRenderer::render($example['blocks']),
                    ]) ?>
                </div>
            <?php else : ?>
                <div class="cms-guide-sample">
                    <div class="cms-guide-sample__label">Aperçu bloc</div>
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
