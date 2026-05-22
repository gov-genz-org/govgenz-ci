<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $sector */
/** @var int|null $nextOrder */

$isEdit = $sector !== null;
$action = $isEdit
    ? site_url('admin/sectors/update/' . (int) ($sector['id'] ?? 0))
    : site_url('admin/sectors/store');

$code = old('code', $isEdit ? (string) ($sector['code'] ?? '') : '');
$codeFr = old('code_fr', $isEdit ? (string) ($sector['code_fr'] ?? '') : '');
$codeEn = old('code_en', $isEdit ? (string) ($sector['code_en'] ?? '') : '');
$labelFr = old('label_fr', $isEdit ? (string) ($sector['label_fr'] ?? '') : '');
$labelEn = old('label_en', $isEdit ? (string) ($sector['label_en'] ?? '') : '');
$email = old('contact_email', $isEdit ? (string) ($sector['contact_email'] ?? '') : '');
$sort = old('sort_order', $isEdit ? (string) (int) ($sector['sort_order'] ?? 0) : (string) ($nextOrder ?? 10));
$active = old('is_active', $isEdit ? (string) ((int) ($sector['is_active'] ?? 1)) : '1');
?>
<h1 class="h3 mb-1"><?= esc($isEdit ? lang('Admin.form_sector_edit') : lang('Admin.form_sector_new')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_sectors_form')) ?></p>

<form method="post" action="<?= esc($action, 'attr') ?>" class="border rounded bg-white shadow-sm p-3 p-md-4" accept-charset="UTF-8">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="sec-code" class="form-label"><?= esc(lang('Admin.form_sector_code')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="code" id="sec-code" class="form-control font-monospace<?= $isEdit ? ' bg-light' : '' ?>"
                   <?= $isEdit ? 'readonly' : 'required' ?> maxlength="32"
                   pattern="[a-z][a-z0-9_-]{0,30}"
                   title="<?= esc(lang('Admin.title_sector_code_pattern'), 'attr') ?>"
                   value="<?= esc($code) ?>">
            <?php if (! $isEdit) : ?>
                <div class="form-text"><?= lang('Admin.help_sector_code_create') ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="sec-sort" class="form-label"><?= esc(lang('Admin.form_sector_sort')) ?></label>
            <input type="number" name="sort_order" id="sec-sort" class="form-control" min="0" max="32767" step="1" value="<?= esc($sort) ?>">
        </div>
        <div class="col-md-4">
            <label for="sec-active" class="form-label"><?= esc(lang('Admin.form_sector_active')) ?></label>
            <select name="is_active" id="sec-active" class="form-select">
                <option value="1" <?= $active === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sector_active_yes')) ?></option>
                <option value="0" <?= $active === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sector_active_no')) ?></option>
            </select>
        </div>
        <div class="col-12 col-md-6">
            <label for="sec-code-fr" class="form-label"><?= esc(lang('Admin.form_sector_code_fr')) ?></label>
            <input type="text" name="code_fr" id="sec-code-fr" class="form-control font-monospace" maxlength="48"
                   pattern="[A-Za-z0-9][A-Za-z0-9_-]{0,47}"
                   title="<?= esc(lang('Admin.title_sector_code_en_pattern'), 'attr') ?>"
                   placeholder="Education"
                   value="<?= esc($codeFr) ?>">
            <div class="form-text"><?= lang('Admin.help_sector_code_fr') ?></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="sec-code-en" class="form-label"><?= esc(lang('Admin.form_sector_code_en')) ?></label>
            <input type="text" name="code_en" id="sec-code-en" class="form-control font-monospace" maxlength="48"
                   pattern="[A-Za-z0-9][A-Za-z0-9_-]{0,47}"
                   title="<?= esc(lang('Admin.title_sector_code_en_pattern'), 'attr') ?>"
                   placeholder="Education"
                   value="<?= esc($codeEn) ?>">
            <div class="form-text"><?= lang('Admin.help_sector_code_en') ?></div>
        </div>
        <div class="col-12">
            <label for="sec-lab-fr" class="form-label"><?= esc(lang('Admin.form_sector_label_fr')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="label_fr" id="sec-lab-fr" class="form-control" required maxlength="255" value="<?= esc($labelFr) ?>">
        </div>
        <div class="col-12">
            <label for="sec-lab-en" class="form-label"><?= esc(lang('Admin.form_sector_label_en')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="label_en" id="sec-lab-en" class="form-control" required maxlength="255" value="<?= esc($labelEn) ?>">
        </div>
        <div class="col-12">
            <label for="sec-mail" class="form-label"><?= esc(lang('Admin.form_sector_email')) ?> <span class="text-danger">*</span></label>
            <input type="email" name="contact_email" id="sec-mail" class="form-control" required maxlength="190" value="<?= esc($email) ?>">
        </div>
    </div>

    <div class="alert alert-light border small mt-4 mb-0"><?= lang('Admin.help_sector_cms_snippet') ?></div>

    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= esc(lang($isEdit ? 'Admin.action_save' : 'Admin.action_create')) ?></button>
        <a class="btn btn-outline-secondary" href="<?= site_url('admin/sectors') ?>"><?= esc(lang('Admin.action_cancel')) ?></a>
    </div>
</form>
