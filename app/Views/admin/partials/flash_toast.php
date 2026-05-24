<?php

declare(strict_types=1);

/** @var string $message */
?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090">
    <div id="adminToastOk" class="toast align-items-center text-bg-success border-0 shadow" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-medium"><?= esc($message) ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
        </div>
    </div>
</div>
<script defer src="<?= base_url('js/admin/flash-toast.js') ?>"></script>
