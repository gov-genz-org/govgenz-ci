<?php

declare(strict_types=1);

/** @var string $prefix budget|material */

$prefix = $prefix ?? 'budget';
$idBase = 'fund-' . $prefix;
$countryName = $prefix . '_phone_country';
$numberName  = $prefix . '_phone_number';
$countryVal  = (string) old($countryName, '+261');
$numberVal   = (string) old($numberName, '');
?>
<div class="project-fund-form__field project-fund-form__field--phone" data-fund-phone-wrap data-fund-phone-prefix="<?= esc($prefix, 'attr') ?>">
    <label class="form-label" for="<?= esc($idBase, 'attr') ?>-phone"><?= esc(lang('Projects.fund_field_phone')) ?></label>
    <input type="hidden" name="<?= esc($countryName, 'attr') ?>" id="<?= esc($idBase, 'attr') ?>-phone-country" value="<?= esc($countryVal, 'attr') ?>" data-fund-phone-country>
    <input
        type="tel"
        class="form-control"
        id="<?= esc($idBase, 'attr') ?>-phone"
        name="<?= esc($numberName, 'attr') ?>"
        value="<?= esc($numberVal) ?>"
        autocomplete="tel-national"
        inputmode="tel"
        maxlength="32"
        data-fund-phone-input
        required
    >
    <p class="project-fund-form__field-error" id="<?= esc($idBase, 'attr') ?>-phone-error" role="alert" hidden></p>
</div>
