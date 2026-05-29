<?php

declare(strict_types=1);

/**
 * Aperçu grille secteurs (aide HTML / blocs) — données BDD ou exemple statique.
 *
 * @var string $html Snippet éditeur (marqueur dynamique ou tuiles statiques)
 */
helper('cms');

$body = cms_sectors_guide_preview_body($html);
?>
<div class="cms-guide-sample__canvas cms-guide-sample__canvas--flush">
    <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell">
        <article class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
            <section class="section section--secteurs">
                <div class="section__inner">
                    <?= $body ?>
                </div>
            </section>
        </article>
    </div>
</div>
