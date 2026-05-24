<?php

declare(strict_types=1);

/** @var string $prefix budget|material */

$prefix   = $prefix ?? 'budget';
$idBase   = 'fund-' . $prefix;
$emailName = $prefix . '_donor_email';
$emailVal  = (string) old($emailName, '');
?>
<div class="project-fund-form__field">
    <label class="form-label" for="<?= esc($idBase, 'attr') ?>-email"><?= esc(lang('Projects.fund_field_email')) ?></label>
    <input
        type="email"
        class="form-control"
        id="<?= esc($idBase, 'attr') ?>-email"
        name="<?= esc($emailName, 'attr') ?>"
        maxlength="190"
        value="<?= esc($emailVal) ?>"
        autocomplete="email"
        inputmode="email"
        placeholder="<?= esc(lang('Projects.fund_field_email_placeholder'), 'attr') ?>"
        data-fund-validate-input
        data-fund-email-input
    >
    <p class="project-fund-form__hint project-fund-form__hint--tight"><?= esc(lang('Projects.fund_field_email_hint')) ?></p>
    <p class="project-fund-form__field-error" id="<?= esc($idBase, 'attr') ?>-email-error" role="alert" hidden></p>
</div>
