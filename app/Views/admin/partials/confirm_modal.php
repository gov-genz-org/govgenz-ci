<?php

declare(strict_types=1);
?>
<div class="modal fade" id="adminConfirmModal" tabindex="-1" aria-labelledby="adminConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5" id="adminConfirmModalLabel">Confirmer</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-2" data-confirm-body>Continuer ?</div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" data-confirm-yes>Oui, confirmer</button>
            </div>
        </div>
    </div>
</div>
<script defer src="<?= base_url('js/admin/confirm-modal.js') ?>"></script>
