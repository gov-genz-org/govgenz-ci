<?php

declare(strict_types=1);

helper(['form', 'admin', 'position']);

use App\Models\PositionItemModel;

/** @var array<string, mixed>|null $item */
/** @var list<array<string, mixed>> $sectors */
/** @var list<array<string, mixed>> $blocksForForm */
/** @var string $bodyContentMode */
/** @var bool $canUseAdvancedHtml */
/** @var string $bodyStoredHtml */
/** @var string|null $publicPreviewUrl */
/** @var array{editUrl: string, publicUrl: ?string, viewLabel: string, editLabel: string}|null $translationPartnerNav */

$blocksForForm = $blocksForForm ?? [];
$bodyContentMode = $bodyContentMode ?? 'blocks';
$canUseAdvancedHtml = $canUseAdvancedHtml ?? false;
$bodyStoredHtml = $bodyStoredHtml ?? '';
$publicPreviewUrl = $publicPreviewUrl ?? null;
$translationPartnerNav = $translationPartnerNav ?? null;
$isEdit = $item !== null;
$action = $isEdit
    ? site_url('admin/position-items/update/' . (int) ($item['id'] ?? 0))
    : site_url('admin/position-items/store');

$oldSectors = old('sectors');
if (is_array($oldSectors)) {
    $selectedSectors = array_values(array_unique(array_filter(array_map(static function ($v): string {
        return is_string($v) ? strtolower(trim($v)) : '';
    }, $oldSectors))));
} elseif ($isEdit) {
    $selectedSectors = array_values(array_unique(array_filter(array_map(static function ($c): string {
        return strtolower(trim((string) $c));
    }, explode(',', (string) ($item['sectors_csv'] ?? ''))))));
} else {
    $selectedSectors = [];
}

$oldTypes = old('types');
if (is_array($oldTypes)) {
    $selectedTypes = array_values(array_unique(array_filter(array_map(static function ($v): string {
        return is_string($v) ? strtolower(trim($v)) : '';
    }, $oldTypes))));
} elseif ($isEdit) {
    $selectedTypes = position_types_from_csv((string) ($item['types_csv'] ?? ''));
} else {
    $selectedTypes = [];
}

$ppContentMode = old('body_content_mode', $bodyContentMode);
if (! in_array($ppContentMode, ['html', 'blocks'], true)) {
    $ppContentMode = 'blocks';
}
$ppLocale = old('locale', $isEdit ? (string) ($item['locale'] ?? 'fr') : 'fr');
if (! in_array($ppLocale, ['fr', 'en'], true)) {
    $ppLocale = 'fr';
}
$ppTg = old('translation_group', $isEdit ? (string) ($item['translation_group'] ?? '') : '');
$typeLabels = position_type_filter_labels($ppLocale);
$typeTipsAdmin = [];
foreach (PositionItemModel::typeCodes() as $typeCode) {
    $typeTipsAdmin[$typeCode] = position_type_tip($typeCode, $ppLocale);
}
?>
<h1 class="h3 mb-1"><?= esc($isEdit ? lang('Admin.form_position_edit') : lang('Admin.form_position_new')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_pi_form_intro')) ?></p>

<?= view('admin/partials/record_form_nav', [
    'publicPreviewUrl'      => $publicPreviewUrl,
    'translationPartnerNav' => $translationPartnerNav,
]) ?>

<form method="post" action="<?= esc($action) ?>" class="admin-editor-form border rounded bg-white shadow-sm p-3 p-md-4">
    <?= csrf_field() ?>

    <?php if ($isEdit) : ?>
        <?= view('admin/partials/record_form_preview', [
            'recordId'         => (int) ($item['id'] ?? 0),
            'draftPreviewPath' => 'admin/position-items/preview-draft',
            'savedPreviewPath' => 'admin/position-items/preview',
        ]) ?>
    <?php endif; ?>

    <?php if (! $canUseAdvancedHtml) : ?>
        <input type="hidden" name="body_content_mode" value="<?= esc($ppContentMode, 'attr') ?>">
    <?php endif; ?>

    <h2 class="h6 text-uppercase text-muted border-bottom pb-2 mb-3"><?= esc(lang('Admin.form_pi_section_meta')) ?></h2>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="pi-slug" class="form-label"><?= esc(lang('Admin.form_pi_slug')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="slug" id="pi-slug" class="form-control" required maxlength="160"
                   value="<?= esc(old('slug', $isEdit ? (string) ($item['slug'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-6">
            <label for="pi-title" class="form-label"><?= esc(lang('Admin.col_title')) ?> <span class="text-danger">*</span></label>
            <input type="text" name="title" id="pi-title" class="form-control" required maxlength="255"
                   value="<?= esc(old('title', $isEdit ? (string) ($item['title'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-6">
            <?= view('admin/partials/record_form_locale', [
                'locale'  => $ppLocale,
                'isEdit'  => $isEdit,
                'fieldId' => 'pi-locale',
            ]) ?>
        </div>
        <?php if ($canUseAdvancedHtml) : ?>
        <div class="col-md-6">
            <label for="pi-tg" class="form-label"><?= esc(lang('Admin.form_pp_translation_group')) ?></label>
            <input type="text" name="translation_group" id="pi-tg" class="form-control font-monospace" maxlength="64"
                   value="<?= esc($ppTg) ?>">
        </div>
        <?php endif; ?>
        <div class="col-12">
            <label for="pi-excerpt" class="form-label"><?= esc(lang('Admin.form_pi_excerpt')) ?></label>
            <textarea name="excerpt" id="pi-excerpt" class="form-control" rows="2"><?= esc(old('excerpt', $isEdit ? (string) ($item['excerpt'] ?? '') : '')) ?></textarea>
        </div>
        <div class="col-12">
            <label for="pi-summary" class="form-label"><?= esc(lang('Admin.form_pi_summary')) ?></label>
            <textarea name="summary" id="pi-summary" class="form-control" rows="4"><?= esc(old('summary', $isEdit ? (string) ($item['summary'] ?? '') : '')) ?></textarea>
        </div>
        <div class="col-md-4">
            <label for="pi-pub" class="form-label"><?= esc(lang('Admin.col_publication')) ?></label>
            <select name="publication_state" id="pi-pub" class="form-select">
                <?php foreach (PositionItemModel::publicationStateLabels() as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= old('publication_state', $isEdit ? (string) ($item['publication_state'] ?? '') : PositionItemModel::PUBLICATION_DRAFT) === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="pi-read" class="form-label"><?= esc(lang('Admin.form_pi_reading')) ?></label>
            <input type="number" name="reading_minutes" id="pi-read" class="form-control" min="0" step="1"
                   value="<?= esc((string) old('reading_minutes', $isEdit ? (string) ($item['reading_minutes'] ?? '') : '')) ?>">
        </div>
        <div class="col-12">
            <label class="form-label"><?= esc(lang('Admin.form_pi_types')) ?></label>
            <p class="form-text mb-2"><?= esc(lang('Admin.form_pi_types_help')) ?></p>
            <div class="row g-2">
                <?php foreach ($typeLabels as $code => $lab) :
                    $tip = $typeTipsAdmin[$code] ?? '';
                    ?>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-check border rounded p-2 h-100 mb-0">
                            <input type="checkbox" name="types[]" value="<?= esc($code) ?>" class="form-check-input"
                                <?= in_array($code, $selectedTypes, true) ? 'checked' : '' ?>>
                            <span class="form-check-label fw-semibold d-block"><?= esc($lab) ?></span>
                            <?php if ($tip !== '') : ?>
                                <span class="d-block small text-muted mt-1"><?= esc($tip) ?></span>
                            <?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-12">
            <label for="pi-sectors" class="form-label"><?= esc(lang('Admin.form_pp_sectors')) ?></label>
            <select name="sectors[]" id="pi-sectors" class="form-select" multiple size="6">
                <?php foreach ($sectors as $s) :
                    $code = strtolower(trim((string) ($s['code'] ?? '')));
                    if ($code === '') {
                        continue;
                    }
                    $sel = in_array($code, $selectedSectors, true) ? ' selected' : '';
                    ?>
                <option value="<?= esc($code) ?>"<?= $sel ?>><?= esc((string) ($s['label_fr'] ?? $code)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="pi-meta-title" class="form-label"><?= esc(lang('Admin.form_pi_meta_title')) ?></label>
            <input type="text" name="meta_title" id="pi-meta-title" class="form-control" maxlength="255"
                   value="<?= esc(old('meta_title', $isEdit ? (string) ($item['meta_title'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-6">
            <label for="pi-meta-desc" class="form-label"><?= esc(lang('Admin.form_pi_meta_desc')) ?></label>
            <input type="text" name="meta_description" id="pi-meta-desc" class="form-control" maxlength="512"
                   value="<?= esc(old('meta_description', $isEdit ? (string) ($item['meta_description'] ?? '') : '')) ?>">
        </div>
    </div>

    <h2 class="h6 text-uppercase text-muted border-bottom pb-2 mb-3 mt-4"><?= esc(lang('Admin.form_pi_section_body')) ?></h2>

    <?php if ($canUseAdvancedHtml) : ?>
    <div class="mb-3">
        <label class="form-label"><?= esc(lang('Admin.form_pi_content_mode')) ?></label>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="body_content_mode" id="pi-mode-blocks" value="blocks" <?= $ppContentMode === 'blocks' ? 'checked' : '' ?> autocomplete="off">
            <label class="btn btn-outline-secondary btn-sm" for="pi-mode-blocks"><?= esc(lang('Admin.form_pi_mode_blocks')) ?></label>
            <input type="radio" class="btn-check" name="body_content_mode" id="pi-mode-html" value="html" <?= $ppContentMode === 'html' ? 'checked' : '' ?> autocomplete="off">
            <label class="btn btn-outline-secondary btn-sm" for="pi-mode-html"><?= esc(lang('Admin.form_pi_mode_html')) ?></label>
        </div>
    </div>
    <div id="pp-html-panel" class="<?= $ppContentMode === 'html' ? '' : 'd-none' ?> mb-3">
        <label for="pp-body" class="form-label"><?= esc(lang('Admin.form_pi_body_html')) ?></label>
        <textarea name="body" id="pp-body" class="form-control" rows="12"><?= esc(old('body', $bodyStoredHtml)) ?></textarea>
    </div>
    <?php endif; ?>

    <?= view('admin/position_items/blocks/builder', [
        'contentMode'        => $ppContentMode,
        'blocksForForm'      => $blocksForForm,
        'canUseAdvancedHtml' => $canUseAdvancedHtml,
        'ppLocale'           => $ppLocale,
    ]) ?>

    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= esc(lang('Admin.action_save')) ?></button>
        <a href="<?= site_url('admin/position-items') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
    </div>
</form>
