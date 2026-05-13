<?php

declare(strict_types=1);

/** @var string $uploadUrl */
/** @var string $mediaJsonUrl */
/** @var string|null $pageUrlContact */
/** @var string|null $pageUrlPress */
/** @var string $editorSelector */
$pageUrlContact = $pageUrlContact ?? site_url('contact');
$pageUrlPress   = $pageUrlPress ?? site_url('press');
$editorSelector  = $editorSelector ?? '#body_html';
?>
<div class="modal fade" id="tinymce-media-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Insérer une image depuis la médiathèque</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p id="tinymce-media-loading" class="text-muted small mb-2">Chargement…</p>
                <p id="tinymce-media-empty" class="text-muted small d-none mb-0">Aucune image dans la médiathèque. Envoyez des fichiers depuis « Médias » puis rechargez cette fenêtre.</p>
                <div id="tinymce-media-grid" class="row g-2"></div>
                <div id="tinymce-media-pager" class="d-none align-items-center justify-content-between gap-2 mt-2 pt-2 border-top small flex-wrap"></div>
            </div>
            <div class="modal-footer">
                <span class="small text-muted me-auto">Seules les images sont listées ici ; pour un PDF, copiez l’URL depuis la médiathèque.</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script defer src="https://cdn.jsdelivr.net/npm/js-beautify@1.15.4/js/lib/beautify.min.js" referrerpolicy="origin"></script>
<script defer src="https://cdn.jsdelivr.net/npm/js-beautify@1.15.4/js/lib/beautify-css.min.js" referrerpolicy="origin"></script>
<script defer src="https://cdn.jsdelivr.net/npm/js-beautify@1.15.4/js/lib/beautify-html.min.js" referrerpolicy="origin"></script>
<?php
$tinymceCfg = [
    'uploadUrl'        => $uploadUrl,
    'mediaJsonUrl'     => $mediaJsonUrl,
    'csrfName'         => csrf_token(),
    'pageUrlContact'   => $pageUrlContact,
    'pageUrlPress'     => $pageUrlPress,
    'editorSelector'   => $editorSelector,
];
?>
<script type="application/json" id="admin-tinymce-config"><?= json_encode($tinymceCfg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?></script>
<script defer src="<?= base_url('js/admin/tinymce-init.js') ?>"></script>
