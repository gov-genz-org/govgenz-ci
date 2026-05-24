<?php

declare(strict_types=1);

helper(['admin']);

/** @var array<string, mixed>|null $post */
/** @var string|null $publicPreviewUrl */
/** @var array{editUrl: string, publicUrl: ?string, viewLabel: string, editLabel: string}|null $translationPartnerNav */

$publicPreviewUrl = $publicPreviewUrl ?? null;
$translationPartnerNav = $translationPartnerNav ?? null;
$isEdit = $post !== null;

$action = $isEdit
    ? site_url('admin/posts/update/' . (int) ($post['id'] ?? 0))
    : site_url('admin/posts/store');
$paRaw = old('published_at', $post !== null ? ($post['published_at'] ?? '') : null);
$paFromForm = is_string($paRaw) && str_contains($paRaw, 'T');
$paValue = $paFromForm ? $paRaw : '';
$paUtcAttr = $paFromForm ? '' : admin_datetime_input_utc_attr($paRaw);
?>
<h1 class="h3 mb-1"><?= esc($isEdit ? lang('Admin.form_post_edit') : lang('Admin.form_post_new')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_post_published_only') ?></p>

<?= view('admin/partials/record_form_nav', [
    'publicPreviewUrl'      => $publicPreviewUrl,
    'translationPartnerNav' => $translationPartnerNav,
]) ?>

<form action="<?= $action ?>" method="post" accept-charset="UTF-8" class="admin-editor-form border rounded bg-white shadow-sm p-3 p-md-4">
    <?= csrf_field() ?>

    <?php if ($isEdit) : ?>
        <?= view('admin/partials/record_form_preview', [
            'recordId'         => (int) ($post['id'] ?? 0),
            'draftPreviewPath' => 'admin/posts/preview-draft',
            'savedPreviewPath' => 'admin/posts/preview',
        ]) ?>
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label" for="slug"><?= esc(lang('Admin.form_label_slug')) ?></label>
        <input type="text" name="slug" id="slug" class="form-control" value="<?= esc(old('slug', $post !== null ? $post['slug'] : '')) ?>" required>
    </div>
    <div class="mb-3">
        <?php
        $postLocale = old('locale', $post !== null ? (string) ($post['locale'] ?? 'fr') : 'fr');
        if (! in_array($postLocale, ['fr', 'en'], true)) {
            $postLocale = 'fr';
        }
        ?>
        <?= view('admin/partials/record_form_locale', [
            'locale'  => $postLocale,
            'isEdit'  => $isEdit,
            'fieldId' => 'locale',
        ]) ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="translation_group"><?= esc(lang('Admin.form_label_translation_group')) ?></label>
        <input type="text" name="translation_group" id="translation_group" class="form-control" maxlength="64" value="<?= esc(old('translation_group', $post !== null ? (string) ($post['translation_group'] ?? '') : '')) ?>" autocomplete="off">
        <div class="form-text"><?= esc(lang('Admin.help_post_translation_group')) ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="title"><?= esc(lang('Admin.form_label_title')) ?></label>
        <input type="text" name="title" id="title" class="form-control" value="<?= esc(old('title', $post !== null ? $post['title'] : '')) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="excerpt"><?= esc(lang('Admin.form_label_excerpt')) ?></label>
        <textarea name="excerpt" id="excerpt" class="form-control" rows="2"><?= esc(old('excerpt', $post !== null ? ($post['excerpt'] ?? '') : '')) ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="body_html"><?= esc(lang('Admin.form_label_post_body')) ?></label>
        <textarea name="body_html" id="body_html" class="form-control" rows="14"><?= old('body_html', $post !== null ? $post['body_html'] : '') ?></textarea>
        <div class="form-text"><?= lang('Admin.help_post_tinymce') ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="status"><?= esc(lang('Admin.form_label_status')) ?></label>
        <select name="status" id="status" class="form-select">
            <?php $st = old('status', $post !== null ? $post['status'] : 'draft'); ?>
            <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_draft')) ?></option>
            <option value="published" <?= $st === 'published' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_published')) ?></option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="published_at"><?= esc(lang('Admin.form_label_published_at')) ?></label>
        <input type="datetime-local" name="published_at" id="published_at" class="form-control" value="<?= esc($paValue) ?>"<?= $paUtcAttr ?>>
    </div>
    <div class="mb-3">
        <label class="form-label" for="meta_title"><?= esc(lang('Admin.form_label_meta_title')) ?></label>
        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= esc(old('meta_title', $post !== null ? ($post['meta_title'] ?? '') : '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="meta_description"><?= esc(lang('Admin.form_label_meta_description')) ?></label>
        <textarea name="meta_description" id="meta_description" class="form-control" rows="2"><?= esc(old('meta_description', $post !== null ? ($post['meta_description'] ?? '') : '')) ?></textarea>
    </div>

    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= esc(lang('Admin.action_save')) ?></button>
        <a href="<?= site_url('admin/posts') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
    </div>
</form>
