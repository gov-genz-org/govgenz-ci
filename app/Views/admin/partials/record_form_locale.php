<?php

declare(strict_types=1);

/** @var string $locale fr|en */
/** @var bool $isEdit */
/** @var string $fieldId */
/** @var string|null $labelKey defaults Admin.form_label_locale */
$labelKey = $labelKey ?? 'Admin.form_label_locale';
if (! in_array($locale, ['fr', 'en'], true)) {
    $locale = 'fr';
}
?>
<label class="form-label" for="<?= esc($fieldId, 'attr') ?>"><?= esc(lang($labelKey)) ?></label>
<?php if ($isEdit) : ?>
    <input type="text" id="<?= esc($fieldId, 'attr') ?>" class="form-control bg-light" readonly
           value="<?= esc($locale === 'en' ? lang('Admin.form_locale_en') : lang('Admin.form_locale_fr')) ?>">
<?php else : ?>
    <select name="locale" id="<?= esc($fieldId, 'attr') ?>" class="form-select" style="max-width:16rem" required>
        <option value="fr" <?= $locale === 'fr' ? 'selected' : '' ?>><?= esc(lang('Admin.form_locale_fr')) ?></option>
        <option value="en" <?= $locale === 'en' ? 'selected' : '' ?>><?= esc(lang('Admin.form_locale_en')) ?></option>
    </select>
<?php endif; ?>
<div class="form-text"><?= esc(lang('Admin.help_record_locale')) ?></div>
