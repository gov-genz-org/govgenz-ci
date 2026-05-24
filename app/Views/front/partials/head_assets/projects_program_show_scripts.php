<?php
/** @var bool $includeFundFormScripts */
$includeFundFormScripts = $includeFundFormScripts ?? false;
?>
<script defer src="<?= esc(public_asset_url('js/front/project-share.js'), 'attr') ?>"></script>
<?php if ($includeFundFormScripts) : ?>
<script defer src="https://cdn.jsdelivr.net/npm/intl-tel-input@25/build/js/intlTelInput.min.js"></script>
<script defer src="<?= esc(public_asset_url('js/front/project-fund-form.js'), 'attr') ?>"></script>
<?php endif ?>
