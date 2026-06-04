<?php

declare(strict_types=1);

helper(['form', 'admin']);

use App\Models\DeclarationItemModel;

/** @var array<string, mixed>|null $item */
/** @var array<string, string> $kindLabels */
/** @var array<string, string> $sectionLabels */
/** @var array<string, string> $pubLabels */
/** @var list<array<string, mixed>> $blocksForForm */
/** @var string $bodyContentMode */
/** @var bool $canUseAdvancedHtml */
/** @var string $bodyStoredHtml */
/** @var string|null $publicListUrl */
/** @var array{editUrl: string, publicUrl: ?string, viewLabel: string, editLabel: string}|null $translationPartnerNav */

$blocksForForm = $blocksForForm ?? [];
$bodyContentMode = $bodyContentMode ?? 'blocks';
$canUseAdvancedHtml = $canUseAdvancedHtml ?? false;
$bodyStoredHtml = $bodyStoredHtml ?? '';
$publicListUrl = $publicListUrl ?? null;
$translationPartnerNav = $translationPartnerNav ?? null;

$isEdit = $item !== null;
$action = $isEdit
    ? site_url('admin/declaration-items/update/' . (int) ($item['id'] ?? 0))
    : site_url('admin/declaration-items/store');
$locale = old('locale', $isEdit ? (string) ($item['locale'] ?? 'fr') : 'fr');
if (! in_array($locale, ['fr', 'en'], true)) {
    $locale = 'fr';
}
$pub = old('publication_state', $isEdit ? (string) ($item['publication_state'] ?? DeclarationItemModel::PUBLICATION_DRAFT) : DeclarationItemModel::PUBLICATION_DRAFT);
$publishedAt = old('published_at', $isEdit && ! empty($item['published_at']) ? date('Y-m-d\TH:i', strtotime((string) $item['published_at'])) : '');
$ppContentMode = old('body_content_mode', $bodyContentMode);
if (! in_array($ppContentMode, ['html', 'blocks'], true)) {
    $ppContentMode = 'blocks';
}
$ppLocale = old('locale', $isEdit ? (string) ($item['locale'] ?? 'fr') : 'fr');
if (! in_array($ppLocale, ['fr', 'en'], true)) {
    $ppLocale = 'fr';
}
?>
<h1 class="h3 mb-1"><?= esc($isEdit ? lang('Admin.form_declaration_edit') : lang('Admin.form_declaration_new')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_declaration_form_intro')) ?></p>

<?= view('admin/partials/record_form_nav', [
    'publicPreviewUrl'      => $publicListUrl,
    'translationPartnerNav' => $translationPartnerNav,
]) ?>

<form method="post" action="<?= esc($action) ?>" class="admin-editor-form border rounded bg-white shadow-sm p-3 p-md-4">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="di-slug" class="form-label"><?= esc(lang('Admin.form_pi_slug')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="slug" id="di-slug" class="form-control" required maxlength="160"
                   value="<?= esc(old('slug', $isEdit ? (string) ($item['slug'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-6">
            <label for="di-title" class="form-label"><?= esc(lang('Admin.col_title')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="title" id="di-title" class="form-control" required maxlength="255"
                   value="<?= esc(old('title', $isEdit ? (string) ($item['title'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-4">
            <?= view('admin/partials/record_form_locale', [
                'locale'  => $locale,
                'isEdit'  => $isEdit,
                'fieldId' => 'di-locale',
            ]) ?>
        </div>
        <div class="col-md-4">
            <label for="di-section" class="form-label"><?= esc(lang('Admin.decl_col_section')) ?> <span class="text-danger">*</span></label>
            <select name="list_section" id="di-section" class="form-select" required>
                <?php foreach ($sectionLabels as $code => $lab) : ?>
                    <option value="<?= esc($code) ?>" <?= old('list_section', $isEdit ? (string) ($item['list_section'] ?? '') : DeclarationItemModel::SECTION_DECLARATIONS) === $code ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="di-kind" class="form-label"><?= esc(lang('Admin.decl_col_kind')) ?> <span class="text-danger">*</span></label>
            <select name="kind" id="di-kind" class="form-select" required>
                <?php foreach ($kindLabels as $code => $lab) : ?>
                    <option value="<?= esc($code) ?>" <?= old('kind', $isEdit ? (string) ($item['kind'] ?? '') : DeclarationItemModel::KIND_OFFICIAL) === $code ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="di-order" class="form-label"><?= esc(lang('Admin.decl_col_order')) ?></label>
            <input type="number" name="sort_order" id="di-order" class="form-control" min="0" step="1"
                   value="<?= esc(old('sort_order', $isEdit ? (string) ($item['sort_order'] ?? '0') : '10')) ?>">
        </div>
        <div class="col-md-4">
            <label for="di-pub" class="form-label"><?= esc(lang('Admin.col_publication')) ?></label>
            <select name="publication_state" id="di-pub" class="form-select">
                <?php foreach ($pubLabels as $code => $lab) : ?>
                    <option value="<?= esc($code) ?>" <?= $pub === $code ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="di-published" class="form-label"><?= esc(lang('Admin.decl_published_at')) ?></label>
            <input type="datetime-local" name="published_at" id="di-published" class="form-control" value="<?= esc($publishedAt) ?>">
            <div class="form-text"><?= esc(lang('Admin.decl_published_at_help')) ?></div>
        </div>
        <div class="col-md-6">
            <label for="di-tg" class="form-label"><?= esc(lang('Admin.form_pp_translation_group')) ?></label>
            <input type="text" name="translation_group" id="di-tg" class="form-control font-monospace" maxlength="64"
                   value="<?= esc(old('translation_group', $isEdit ? (string) ($item['translation_group'] ?? '') : '')) ?>">
        </div>
        <div class="col-12">
            <label for="di-summary" class="form-label"><?= esc(lang('Admin.decl_summary')) ?></label>
            <textarea name="summary" id="di-summary" class="form-control" rows="4"><?= esc(old('summary', $isEdit ? (string) ($item['summary'] ?? '') : '')) ?></textarea>
        </div>
        <div class="col-md-6">
            <label for="di-meta" class="form-label"><?= esc(lang('Admin.decl_meta_line')) ?></label>
            <input type="text" name="meta_line" id="di-meta" class="form-control" maxlength="255"
                   value="<?= esc(old('meta_line', $isEdit ? (string) ($item['meta_line'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-6">
            <label for="di-band" class="form-label"><?= esc(lang('Admin.decl_band_label')) ?></label>
            <input type="text" name="band_label" id="di-band" class="form-control" maxlength="120"
                   value="<?= esc(old('band_label', $isEdit ? (string) ($item['band_label'] ?? '') : '')) ?>">
            <div class="form-text"><?= esc(lang('Admin.decl_band_label_help')) ?></div>
        </div>
        <div class="col-md-4">
            <label for="di-badge" class="form-label"><?= esc(lang('Admin.decl_badge_label')) ?></label>
            <input type="text" name="badge_label" id="di-badge" class="form-control" maxlength="120"
                   value="<?= esc(old('badge_label', $isEdit ? (string) ($item['badge_label'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-4">
            <label for="di-cta-label" class="form-label"><?= esc(lang('Admin.decl_cta_label')) ?></label>
            <input type="text" name="cta_label" id="di-cta-label" class="form-control" maxlength="120"
                   value="<?= esc(old('cta_label', $isEdit ? (string) ($item['cta_label'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-4">
            <label for="di-cta-href" class="form-label"><?= esc(lang('Admin.decl_cta_href')) ?></label>
            <input type="text" name="cta_href" id="di-cta-href" class="form-control" maxlength="512"
                   value="<?= esc(old('cta_href', $isEdit ? (string) ($item['cta_href'] ?? '') : 'mailto:contact@govgenz.org')) ?>">
        </div>
    </div>

    <h2 class="h6 text-uppercase text-muted border-bottom pb-2 mb-3 mt-4"><?= esc(lang('Admin.form_declaration_section_body')) ?></h2>

    <?php if ($canUseAdvancedHtml) : ?>
    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Admin.form_pi_content_mode')) ?></label>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="body_content_mode" id="di-mode-blocks" value="blocks" <?= $ppContentMode === 'blocks' ? 'checked' : '' ?> autocomplete="off">
            <label class="btn btn-outline-secondary btn-sm" for="di-mode-blocks"><?= esc(lang('Admin.form_pi_mode_blocks')) ?></label>
            <input type="radio" class="btn-check" name="body_content_mode" id="di-mode-html" value="html" <?= $ppContentMode === 'html' ? 'checked' : '' ?> autocomplete="off">
            <label class="btn btn-outline-secondary btn-sm" for="di-mode-html"><?= esc(lang('Admin.form_pi_mode_html')) ?></label>
        </div>
    </div>
    <div id="pp-html-panel" class="<?= $ppContentMode === 'html' ? '' : 'd-none' ?> mb-3">
        <label for="pp-body" class="form-label"><?= esc(lang('Admin.form_pi_body_html')) ?></label>
        <textarea name="body" id="pp-body" class="form-control" rows="12"><?= esc(old('body', $bodyStoredHtml)) ?></textarea>
    </div>
    <?php endif; ?>

    <?= view('admin/declaration_items/blocks/builder', [
        'contentMode'        => $ppContentMode,
        'blocksForForm'      => $blocksForForm,
        'canUseAdvancedHtml' => $canUseAdvancedHtml,
        'ppLocale'           => $ppLocale,
    ]) ?>

    <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-primary"><?= esc(lang('Admin.action_save')) ?></button>
        <a href="<?= site_url('admin/declaration-items') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
    </div>
</form>
