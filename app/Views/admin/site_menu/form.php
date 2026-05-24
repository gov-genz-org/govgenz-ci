<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $item */

$action = $item ? site_url('admin/site-menu/update/' . (int) $item['id']) : site_url('admin/site-menu/store');

$kind = old('href_kind', $item !== null ? (string) ($item['href_kind'] ?? 'segment') : 'segment');
$target = old('href_target', $item !== null ? (string) ($item['href_target'] ?? '') : '');
$mk = old('match_key', $item !== null ? (string) ($item['match_key'] ?? '') : '');
$sort = old('sort_order', $item !== null ? (string) ($item['sort_order'] ?? '0') : '0');
$active = old('is_active', $item !== null ? (string) ((int) ($item['is_active'] ?? 1)) : '1');
$cssClass = old('css_class', $item !== null ? (string) ($item['css_class'] ?? '') : '');
$localeSel = old('locale', $item !== null ? (string) ($item['locale'] ?? 'fr') : 'fr');
if (! in_array($localeSel, ['fr', 'en'], true)) {
    $localeSel = 'fr';
}
?>
<h1 class="h3 mb-1"><?= esc($item ? lang('Admin.form_menu_edit') : lang('Admin.form_menu_new')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_sitemenu_form')) ?></p>

<form action="<?= esc($action, 'attr') ?>" method="post" accept-charset="UTF-8" class="admin-editor-form border rounded bg-white shadow-sm p-3 p-md-4">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label" for="label"><?= esc(lang('Admin.form_sitemenu_label')) ?></label>
        <input type="text" name="label" id="label" class="form-control" required maxlength="255" value="<?= esc(old('label', $item !== null ? (string) ($item['label'] ?? '') : '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="locale"><?= esc(lang('Admin.form_sitemenu_locale')) ?></label>
        <select name="locale" id="locale" class="form-select" style="max-width:16rem">
            <option value="fr" <?= $localeSel === 'fr' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_locale_fr')) ?></option>
            <option value="en" <?= $localeSel === 'en' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_locale_en')) ?></option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="sort_order"><?= esc(lang('Admin.form_sitemenu_sort')) ?></label>
        <input type="number" name="sort_order" id="sort_order" class="form-control" style="max-width:12rem" min="0" step="1" value="<?= esc($sort) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="href_kind"><?= esc(lang('Admin.form_sitemenu_href_kind')) ?></label>
        <select name="href_kind" id="href_kind" class="form-select" style="max-width:28rem">
            <option value="home" <?= $kind === 'home' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_kind_home')) ?></option>
            <option value="segment" <?= $kind === 'segment' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_kind_segment')) ?></option>
            <option value="path" <?= $kind === 'path' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_kind_path')) ?></option>
            <option value="external" <?= $kind === 'external' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_kind_external')) ?></option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="href_target"><?= esc(lang('Admin.form_sitemenu_target')) ?></label>
        <input type="text" name="href_target" id="href_target" class="form-control" maxlength="512" value="<?= esc($target) ?>" placeholder="<?= esc(lang('Admin.ph_menu_href_target'), 'attr') ?>">
        <div class="form-text"><?= esc(lang('Admin.form_sitemenu_target_help')) ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="match_key"><?= esc(lang('Admin.form_sitemenu_match_key')) ?></label>
        <input type="text" name="match_key" id="match_key" class="form-control" required maxlength="190" pattern="[a-z0-9_-]+" value="<?= esc($mk) ?>">
        <div class="form-text"><?= esc(lang('Admin.form_sitemenu_match_key_help')) ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="css_class"><?= esc(lang('Admin.form_sitemenu_css')) ?></label>
        <input type="text" name="css_class" id="css_class" class="form-control" maxlength="80" value="<?= esc($cssClass) ?>" placeholder="Ex. ggz-nav-admin">
        <div class="form-text"><?= esc(lang('Admin.form_sitemenu_css_help')) ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="is_active"><?= esc(lang('Admin.form_sitemenu_visible')) ?></label>
        <select name="is_active" id="is_active" class="form-select" style="max-width:16rem">
            <option value="1" <?= $active === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_yes')) ?></option>
            <option value="0" <?= $active === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_no')) ?></option>
        </select>
    </div>
    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= esc($item ? lang('Admin.action_save') : lang('Admin.action_create')) ?></button>
        <a href="<?= site_url('admin/site-menu') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
    </div>
</form>
