<?php

declare(strict_types=1);
?>
<div class="modal fade" id="adminConfirmModal" tabindex="-1" aria-labelledby="adminConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5" id="adminConfirmModalLabel"><?= esc(lang('Admin.ui_confirm_title')) ?></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('Admin.ui_close'), 'attr') ?>"></button>
            </div>
            <div class="modal-body pt-2" data-confirm-body><?= esc(lang('Admin.ui_confirm_body')) ?></div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= esc(lang('Admin.ui_confirm_cancel')) ?></button>
                <button type="button" class="btn btn-danger" data-confirm-yes><?= esc(lang('Admin.ui_confirm_yes')) ?></button>
            </div>
        </div>
    </div>
</div>
<script defer src="<?= base_url('js/admin/confirm-modal.js') ?>"></script>
