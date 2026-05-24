<?php

declare(strict_types=1);

/** @var string $uploadUrl */
?>
<script defer src="https://cdn.jsdelivr.net/npm/dropzone@5/dist/min/dropzone.min.js"></script>
<?php
$dzCfg = [
    'uploadUrl' => $uploadUrl,
    'csrfName'  => csrf_token(),
];
?>
<script type="application/json" id="admin-media-dropzone-config"><?= json_encode($dzCfg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?></script>
<script defer src="<?= base_url('js/admin/media-dropzone.js') ?>"></script>
