<?php

declare(strict_types=1);

helper(['admin', 'cms']);

$layoutState = cms_layout_select_state(old('layout_key', $page !== null ? ($page['layout_key'] ?? '') : ''));

$contentMode = $contentMode ?? 'html';
$blocksForForm = $blocksForForm ?? [];
$publicPreviewUrl = $publicPreviewUrl ?? null;
$translationPartnerNav = $translationPartnerNav ?? null;
$listHeroKind = $listHeroKind ?? null;
$listHeroCreateKind = $listHeroCreateKind ?? null;
$listHeroDefaultLocale = $listHeroDefaultLocale ?? 'fr';
$listHeroDefaultTranslationGroup = $listHeroDefaultTranslationGroup ?? null;
$isListHeroForm = $listHeroKind !== null;

$action = $page
    ? site_url('admin/pages/update/' . $page['id'])
    : site_url('admin/pages/store');
$isEdit = $page !== null;
?>
<h1 class="h3 mb-1"><?= esc($isEdit ? lang('Admin.form_page_edit') : lang('Admin.form_page_new')) ?></h1>
<p class="text-muted small mb-3"><?= $isListHeroForm ? lang('Admin.help_list_hero_form_intro') : lang('Admin.help_pages_form_intro') ?></p>

<?= view('admin/partials/record_form_nav', [
    'publicPreviewUrl'      => $publicPreviewUrl,
    'translationPartnerNav' => $translationPartnerNav,
]) ?>

<form action="<?= $action ?>" method="post" accept-charset="UTF-8" class="admin-editor-form border rounded bg-white shadow-sm p-3 p-md-4">
    <?= csrf_field() ?>
    <?php if ($isEdit && ! $isListHeroForm) : ?>
        <?= view('admin/partials/record_form_preview', [
            'recordId'         => (int) ($page['id'] ?? 0),
            'draftPreviewPath' => 'admin/pages/preview-draft',
            'savedPreviewPath' => 'admin/pages/preview',
        ]) ?>
    <?php endif; ?>
    <?php if ($isListHeroForm && ! $isEdit) : ?>
        <input type="hidden" name="list_hero_kind" value="<?= esc($listHeroKind, 'attr') ?>">
    <?php endif; ?>
    <?php if ($isListHeroForm) : ?>
        <input type="hidden" name="content_mode" value="html">
        <input type="hidden" name="body_html" value="">
    <?php endif; ?>
    <?php
    $slugField = strtolower(trim((string) old('slug', $page !== null ? (string) ($page['slug'] ?? '') : '')));
    if ($isListHeroForm) {
        $slugField = cms_list_hero_canonical_slug($listHeroKind);
    }
    $listHeroSlugLocked = $isListHeroForm;
    ?>
    <div class="mb-3">
        <?php if ($listHeroSlugLocked) : ?>
            <input type="hidden" name="slug" value="<?= esc($slugField, 'attr') ?>">
            <p class="form-label mb-1"><?= esc(lang('Admin.form_label_slug_pages')) ?></p>
            <p class="mb-0"><code><?= esc($slugField) ?></code> <span class="text-muted small">(<?= esc(lang('Admin.label_list_hero_slug_fixed')) ?>)</span></p>
        <?php else : ?>
            <label class="form-label" for="slug"><?= esc(lang('Admin.form_label_slug_pages')) ?></label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?= esc(old('slug', $page !== null ? $page['slug'] : '')) ?>" required>
        <?php endif; ?>
        <?php if ($listHeroKind === 'projects') : ?>
            <div class="alert alert-info border py-2 small mt-2 mb-0" role="status">
                <strong><?= esc(lang('Admin.alert_page_projects_program_title')) ?></strong>
                <?= lang('Admin.alert_page_projects_program_body') ?>
                <?php
                $lpFr = admin_public_projects_list_url('fr');
                $lpEn = admin_public_projects_list_url('en');
                ?>
                <span class="d-block mt-2">
                    <a href="<?= esc($lpFr, 'attr') ?>" target="_blank" rel="noopener" class="me-2"><?= esc(lang('Admin.action_view_public_list_fr')) ?></a>
                    <a href="<?= esc($lpEn, 'attr') ?>" target="_blank" rel="noopener"><?= esc(lang('Admin.action_view_public_list_en')) ?></a>
                </span>
            </div>
        <?php elseif ($listHeroKind === 'positions') : ?>
            <div class="alert alert-info border py-2 small mt-2 mb-0" role="status">
                <strong><?= esc(lang('Admin.alert_page_positions_program_title')) ?></strong>
                <?= lang('Admin.alert_page_positions_program_body') ?>
                <?php
                $lpFr = admin_public_positions_list_url('fr');
                $lpEn = admin_public_positions_list_url('en');
                ?>
                <span class="d-block mt-2">
                    <a href="<?= esc($lpFr, 'attr') ?>" target="_blank" rel="noopener" class="me-2"><?= esc(lang('Admin.action_view_public_list_fr')) ?></a>
                    <a href="<?= esc($lpEn, 'attr') ?>" target="_blank" rel="noopener"><?= esc(lang('Admin.action_view_public_list_en')) ?></a>
                </span>
            </div>
        <?php elseif ($listHeroKind === 'press') : ?>
            <div class="alert alert-info border py-2 small mt-2 mb-0" role="status">
                <strong><?= esc(lang('Admin.alert_page_press_program_title')) ?></strong>
                <?= lang('Admin.alert_page_press_program_body') ?>
                <?php
                $lpFr = admin_public_press_list_url('fr');
                $lpEn = admin_public_press_list_url('en');
                ?>
                <span class="d-block mt-2">
                    <a href="<?= esc($lpFr, 'attr') ?>" target="_blank" rel="noopener" class="me-2"><?= esc(lang('Admin.action_view_public_list_fr')) ?></a>
                    <a href="<?= esc($lpEn, 'attr') ?>" target="_blank" rel="noopener"><?= esc(lang('Admin.action_view_public_list_en')) ?></a>
                </span>
            </div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <?php
        $pageLocale = old('locale', $page !== null ? (string) ($page['locale'] ?? 'fr') : $listHeroDefaultLocale);
        if (! in_array($pageLocale, ['fr', 'en'], true)) {
            $pageLocale = 'fr';
        }
        ?>
        <?= view('admin/partials/record_form_locale', [
            'locale'  => $pageLocale,
            'isEdit'  => $isEdit,
            'fieldId' => 'locale',
        ]) ?>
    </div>
    <?php
    $tgrpValue = old('translation_group', $page !== null
        ? (string) ($page['translation_group'] ?? '')
        : (string) ($listHeroDefaultTranslationGroup ?? ''));
    ?>
    <?php if ($isListHeroForm) : ?>
        <input type="hidden" name="translation_group" value="<?= esc($tgrpValue, 'attr') ?>">
    <?php else : ?>
        <div class="mb-3">
            <label class="form-label" for="translation_group"><?= esc(lang('Admin.form_label_translation_group')) ?></label>
            <input type="text" name="translation_group" id="translation_group" class="form-control" maxlength="64" value="<?= esc($tgrpValue) ?>" autocomplete="off">
            <div class="form-text"><?= esc(lang('Admin.help_page_translation_group')) ?></div>
        </div>
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label" for="title"><?= esc(lang('Admin.form_label_title')) ?></label>
        <input type="text" name="title" id="title" class="form-control" value="<?= esc(old('title', $page !== null ? $page['title'] : '')) ?>" required>
        <?php if ($isListHeroForm) : ?>
            <div class="form-text"><?= esc(lang('Admin.help_list_hero_admin_title')) ?></div>
        <?php endif; ?>
    </div>

    <div class="border rounded p-3 mb-3 bg-light-subtle">
        <p class="fw-semibold mb-2"><?= esc(lang('Admin.form_page_hero_section')) ?></p>
        <p class="text-muted small mb-3"><?= esc($isListHeroForm ? lang('Admin.help_list_hero_fields_intro') : lang('Admin.help_page_hero_intro')) ?></p>
        <div class="mb-3">
            <label class="form-label" for="hero_overline"><?= esc(lang('Admin.form_page_hero_overline')) ?></label>
            <input type="text" name="hero_overline" id="hero_overline" class="form-control" maxlength="255" value="<?= esc(old('hero_overline', $page !== null ? (string) ($page['hero_overline'] ?? '') : '')) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label" for="hero_title"><?= esc(lang('Admin.form_page_hero_title')) ?></label>
            <input type="text" name="hero_title" id="hero_title" class="form-control" maxlength="255" value="<?= esc(old('hero_title', $page !== null ? (string) ($page['hero_title'] ?? '') : '')) ?>">
            <div class="form-text"><?= esc(lang('Admin.help_page_hero_title_empty')) ?></div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="hero_lead"><?= esc(lang('Admin.form_page_hero_lead')) ?></label>
            <textarea name="hero_lead" id="hero_lead" class="form-control" rows="3"><?= esc(old('hero_lead', $page !== null ? (string) ($page['hero_lead'] ?? '') : '')) ?></textarea>
        </div>
        <?php if (! $isListHeroForm) : ?>
            <div class="mb-3">
                <label class="form-label" for="hero_image_id"><?= esc(lang('Admin.form_page_hero_image_id')) ?></label>
                <input type="number" name="hero_image_id" id="hero_image_id" class="form-control" style="max-width:12rem" min="1" step="1" value="<?= esc(old('hero_image_id', $page !== null && ! empty($page['hero_image_id']) ? (string) $page['hero_image_id'] : '')) ?>">
                <div class="form-text"><?= lang('Admin.help_page_hero_media', [site_url('admin/media')]) ?></div>
            </div>
            <div class="mb-0">
                <label class="form-label" for="hero_image_alt"><?= esc(lang('Admin.form_page_hero_image_alt')) ?></label>
                <input type="text" name="hero_image_alt" id="hero_image_alt" class="form-control" maxlength="255" value="<?= esc(old('hero_image_alt', $page !== null ? (string) ($page['hero_image_alt'] ?? '') : '')) ?>">
            </div>
        <?php endif; ?>
    </div>

    <?php if (! $isListHeroForm) : ?>
    <div class="mb-3">
        <label class="form-label d-block"><?= esc(lang('Admin.form_page_main_content')) ?></label>
        <div class="btn-group flex-wrap" role="group" aria-label="<?= esc(lang('Admin.aria_content_mode'), 'attr') ?>">
            <input type="radio" class="btn-check" name="content_mode" id="cm_html" value="html" autocomplete="off" <?= $contentMode === 'html' ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary btn-sm" for="cm_html"><?= esc(lang('Admin.form_page_mode_html')) ?></label>
            <input type="radio" class="btn-check" name="content_mode" id="cm_blocks" value="blocks" autocomplete="off" <?= $contentMode === 'blocks' ? 'checked' : '' ?>>
            <label class="btn btn-outline-secondary btn-sm" for="cm_blocks"><?= esc(lang('Admin.form_page_mode_blocks')) ?></label>
        </div>
        <div class="form-text"><?= esc(lang('Admin.help_page_content_mode_switch')) ?></div>
    </div>

    <div id="cms-html-panel" class="<?= $contentMode === 'blocks' ? 'd-none' : '' ?>">
        <div class="mb-3">
            <label class="form-label" for="body_html"><?= esc(lang('Admin.form_label_body_html')) ?></label>
            <textarea name="body_html" id="body_html" class="form-control" rows="14"><?= old('body_html', $page !== null ? ($page['body_html'] ?? '') : '') ?></textarea>
            <div class="form-text"><?= lang('Admin.help_pages_tinymce') ?></div>
        </div>
    </div>

    <?= view('admin/pages/blocks_builder', ['blocksForForm' => $blocksForForm, 'contentMode' => $contentMode]) ?>
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label" for="status"><?= esc(lang('Admin.form_label_status')) ?></label>
        <select name="status" id="status" class="form-select">
            <?php
            $st = old('status', $page !== null ? $page['status'] : 'draft');
            ?>
            <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_draft')) ?></option>
            <option value="published" <?= $st === 'published' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_published')) ?></option>
        </select>
    </div>
    <?php if (! $isListHeroForm) : ?>
    <div class="mb-3">
        <label class="form-label" for="layout_key"><?= esc(lang('Admin.form_page_layout')) ?></label>
        <select name="layout_key" id="layout_key" class="form-select" style="max-width: 28rem;">
            <?php if ($layoutState['legacy']) : ?>
                <option value="<?= esc($layoutState['value'], 'attr') ?>" selected><?= esc(lang('Admin.form_page_layout_legacy', [$layoutState['value']])) ?></option>
            <?php endif; ?>
            <option value="" <?= ! $layoutState['legacy'] && $layoutState['value'] === '' ? 'selected' : '' ?>><?= esc(lang('Admin.form_page_layout_default')) ?></option>
            <option value="narrow" <?= ! $layoutState['legacy'] && $layoutState['value'] === 'narrow' ? 'selected' : '' ?>><?= esc(lang('Admin.form_page_layout_narrow')) ?></option>
            <option value="full" <?= ! $layoutState['legacy'] && $layoutState['value'] === 'full' ? 'selected' : '' ?>><?= esc(lang('Admin.form_page_layout_full')) ?></option>
        </select>
        <div class="form-text"><?= esc(lang('Admin.help_page_layout')) ?></div>
    </div>
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label" for="meta_title"><?= esc(lang('Admin.form_label_meta_title')) ?></label>
        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= esc(old('meta_title', $page !== null ? ($page['meta_title'] ?? '') : '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="meta_description"><?= esc(lang('Admin.form_label_meta_description')) ?></label>
        <textarea name="meta_description" id="meta_description" class="form-control" rows="2"><?= esc(old('meta_description', $page !== null ? ($page['meta_description'] ?? '') : '')) ?></textarea>
    </div>

    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= esc(lang('Admin.action_save')) ?></button>
        <a href="<?= site_url('admin/pages') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
    </div>
</form>
