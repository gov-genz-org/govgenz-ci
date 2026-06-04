<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $unit */
/** @var int|null $nextOrder */

$isEdit = $unit !== null;
$action = $isEdit
    ? site_url('admin/structures/update/' . (int) ($unit['id'] ?? 0))
    : site_url('admin/structures/store');
?>
<h1 class="h3 mb-1"><?= esc($isEdit ? lang('Admin.form_structure_edit') : lang('Admin.form_structure_new')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_structures_form')) ?></p>

<form method="post" action="<?= esc($action, 'attr') ?>" class="admin-editor-form border rounded bg-white shadow-sm p-3 p-md-4" accept-charset="UTF-8">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="struct-code" class="form-label"><?= esc(lang('Admin.form_structure_code')) ?> <?php if (! $isEdit) : ?><span class="text-danger">*</span><?php endif; ?></label>
            <input type="text" name="code" id="struct-code" class="form-control font-monospace<?= $isEdit ? ' bg-light' : '' ?>"
                   <?= $isEdit ? 'readonly' : 'required' ?> maxlength="32"
                   pattern="[a-z][a-z0-9_-]{0,30}"
                   value="<?= esc(old('code', $isEdit ? (string) ($unit['code'] ?? '') : '')) ?>">
            <?php if (! $isEdit) : ?>
                <div class="form-text"><?= esc(lang('Admin.help_structure_code_create')) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="struct-sort" class="form-label"><?= esc(lang('Admin.form_structure_sort')) ?></label>
            <input type="number" name="sort_order" id="struct-sort" class="form-control" min="0" max="32767" step="1"
                   value="<?= esc(old('sort_order', $isEdit ? (string) (int) ($unit['sort_order'] ?? 0) : (string) ($nextOrder ?? 10))) ?>">
        </div>
        <div class="col-md-4">
            <label for="struct-active" class="form-label"><?= esc(lang('Admin.form_structure_active')) ?></label>
            <?php $active = old('is_active', $isEdit ? (string) ((int) ($unit['is_active'] ?? 1)) : '1'); ?>
            <select name="is_active" id="struct-active" class="form-select">
                <option value="1" <?= $active === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_structure_active_yes')) ?></option>
                <option value="0" <?= $active === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_structure_active_no')) ?></option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="struct-role" class="form-label"><?= esc(lang('Admin.form_structure_role')) ?></label>
            <?php $role = old('unit_role', $isEdit ? (string) ($unit['unit_role'] ?? 'function') : 'function'); ?>
            <select name="unit_role" id="struct-role" class="form-select">
                <option value="function" <?= $role === 'function' ? 'selected' : '' ?>><?= esc(lang('Admin.structure_role_function')) ?></option>
                <option value="core" <?= $role === 'core' ? 'selected' : '' ?>><?= esc(lang('Admin.structure_role_core')) ?></option>
            </select>
            <div class="form-text"><?= esc(lang('Admin.help_structure_role')) ?></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="struct-title-fr" class="form-label"><?= esc(lang('Admin.form_structure_title_fr')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="title_fr" id="struct-title-fr" class="form-control" required maxlength="255"
                   value="<?= esc(old('title_fr', $isEdit ? (string) ($unit['title_fr'] ?? '') : '')) ?>">
        </div>
        <div class="col-12 col-md-6">
            <label for="struct-title-en" class="form-label"><?= esc(lang('Admin.form_structure_title_en')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="title_en" id="struct-title-en" class="form-control" required maxlength="255"
                   value="<?= esc(old('title_en', $isEdit ? (string) ($unit['title_en'] ?? '') : '')) ?>">
        </div>
        <div class="col-12 col-md-6">
            <label for="struct-sub-fr" class="form-label"><?= esc(lang('Admin.form_structure_subtitle_fr')) ?></label>
            <input type="text" name="subtitle_fr" id="struct-sub-fr" class="form-control" maxlength="255"
                   value="<?= esc(old('subtitle_fr', $isEdit ? (string) ($unit['subtitle_fr'] ?? '') : '')) ?>">
            <div class="form-text"><?= esc(lang('Admin.help_structure_subtitle')) ?></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="struct-sub-en" class="form-label"><?= esc(lang('Admin.form_structure_subtitle_en')) ?></label>
            <input type="text" name="subtitle_en" id="struct-sub-en" class="form-control" maxlength="255"
                   value="<?= esc(old('subtitle_en', $isEdit ? (string) ($unit['subtitle_en'] ?? '') : '')) ?>">
        </div>
        <div class="col-12">
            <label for="struct-desc-fr" class="form-label"><?= esc(lang('Admin.form_structure_description_fr')) ?></label>
            <textarea name="description_fr" id="struct-desc-fr" class="form-control" rows="3"><?= esc(old('description_fr', $isEdit ? (string) ($unit['description_fr'] ?? '') : '')) ?></textarea>
        </div>
        <div class="col-12">
            <label for="struct-desc-en" class="form-label"><?= esc(lang('Admin.form_structure_description_en')) ?></label>
            <textarea name="description_en" id="struct-desc-en" class="form-control" rows="3"><?= esc(old('description_en', $isEdit ? (string) ($unit['description_en'] ?? '') : '')) ?></textarea>
        </div>
        <div class="col-12">
            <label for="struct-mail" class="form-label"><?= esc(lang('Admin.form_structure_email')) ?> <span class="text-danger">*</span></label>
            <input type="email" name="contact_email" id="struct-mail" class="form-control" required maxlength="190"
                   value="<?= esc(old('contact_email', $isEdit ? (string) ($unit['contact_email'] ?? '') : '')) ?>">
        </div>
        <div class="col-12 col-md-6">
            <?= view('admin/partials/media_slot_single', [
                'idInputName'  => 'media_id',
                'altInputName' => 'media_alt',
                'mediaId'      => (int) old('media_id', $isEdit ? (int) ($unit['media_id'] ?? 0) : 0),
                'mediaAlt'     => (string) old('media_alt', $isEdit ? (string) ($unit['media_alt'] ?? '') : ''),
            ]) ?>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><?= esc(lang('Admin.action_save')) ?></button>
        <a href="<?= site_url('admin/structures') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
    </div>
</form>
