<?php

declare(strict_types=1);

/**
 * Aperçu grille structures (aide blocs Pages) — rendu identique au site public.
 *
 * @var string $html HTML rendu par CmsBodyBlocksRenderer
 */
?>
<div class="cms-guide-sample__canvas cms-guide-sample__canvas--flush">
    <div class="ggz-public-theme cms-guide-preview-host ggz-main-shell">
        <article class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
            <?= $html ?>
        </article>
    </div>
</div>
