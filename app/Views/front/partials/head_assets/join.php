<?php

declare(strict_types=1);

helper('asset');
$multiSelectBase = base_url('assets/vendor/multi-select-dropdown-js/1.0.3/');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@25/build/css/intlTelInput.css">
<link rel="stylesheet" href="<?= esc($multiSelectBase, 'attr') ?>MultiSelect.min.css">
<link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/join-enhancements.css'), 'attr') ?>">
