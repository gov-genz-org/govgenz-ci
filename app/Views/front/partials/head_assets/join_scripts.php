<?php

declare(strict_types=1);

helper('asset');
$multiSelectBase = base_url('assets/vendor/multi-select-dropdown-js/1.0.3/');
?>
<script defer src="https://cdn.jsdelivr.net/npm/intl-tel-input@25/build/js/intlTelInput.min.js"></script>
<script defer src="<?= esc($multiSelectBase, 'attr') ?>MultiSelect.min.js"></script>
<script defer src="<?= esc(public_asset_url('js/front/join-enhancements.js'), 'attr') ?>"></script>
