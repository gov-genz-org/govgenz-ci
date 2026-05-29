<?php

declare(strict_types=1);

use App\Libraries\CmsBodyBlocksRenderer;

/** @var list<array{id:string,title:string,usage:string,blocks:list<array<string,mixed>>}> $examples */
?>
<div class="mb-4 admin-cms-guide-lead">
    <h1 class="h3 mb-2">Guide des blocs Pages</h1>
    <p class="text-muted mb-0">
        Cette page montre des exemples de rendu pour chaque bloc du Page Builder.
        Home et Footer restent des contenus globaux du site (pas des blocs Pages standards).
    </p>
</div>

<?php foreach ($examples as $example) : ?>
    <div class="card mb-4" id="admin-block-<?= esc($example['id'], 'attr') ?>">
        <div class="card-body">
            <h2 class="h5 card-title mb-1"><?= esc($example['title']) ?></h2>
            <p class="card-text small text-muted mb-3"><?= esc($example['usage']) ?></p>

            <label class="form-label small fw-semibold mb-1 text-muted">Exemple de donnees (JSON)</label>
            <textarea class="form-control font-monospace small mb-3" rows="8" readonly spellcheck="false"><?= esc((string) json_encode($example['blocks'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>

            <label class="form-label small fw-semibold mb-1 text-muted">Rendu</label>
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
        </div>
    </div>
<?php endforeach; ?>

