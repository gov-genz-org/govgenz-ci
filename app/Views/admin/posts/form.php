<?php

declare(strict_types=1);

helper(['admin']);

$action = $post
    ? site_url('admin/posts/update/' . $post['id'])
    : site_url('admin/posts/store');
$paRaw = old('published_at', $post !== null ? ($post['published_at'] ?? '') : null);
$paFromForm = is_string($paRaw) && str_contains($paRaw, 'T');
$paValue = $paFromForm ? $paRaw : '';
$paUtcAttr = $paFromForm ? '' : admin_datetime_input_utc_attr($paRaw);

$previewUrl = null;
if ($post !== null && ($post['status'] ?? '') === 'published' && ($post['slug'] ?? '') !== '') {
    $previewUrl = admin_public_press_url((string) ($post['slug'] ?? ''), (string) ($post['locale'] ?? 'fr'));
}
?>
<h1 class="h3 mb-1"><?= esc($post ? lang('Admin.form_post_edit') : lang('Admin.form_post_new')) ?></h1>
<p class="text-muted small mb-3">Article visible sur le site uniquement lorsque le statut est <strong>Publié</strong>.</p>

<?php if ($previewUrl !== null) : ?>
    <div class="alert alert-light border py-2 small mb-3">
        <strong>Aperçu public :</strong>
        <a href="<?= esc($previewUrl) ?>" target="_blank" rel="noopener" class="ms-1"><?= esc($previewUrl) ?></a>
    </div>
<?php endif; ?>

<?php if ($post !== null) : ?>
    <div class="alert alert-secondary border py-2 small mb-3 d-flex flex-wrap align-items-center gap-2 justify-content-between">
        <span class="mb-0"><strong>Aperçu brouillon</strong> — rendu comme une page presse, même si l’article n’est pas publié.</span>
        <a href="<?= site_url('admin/posts/preview/' . (int) $post['id']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark flex-shrink-0">Ouvrir l’aperçu</a>
    </div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" accept-charset="UTF-8" class="admin-editor-form">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label" for="slug">Slug</label>
        <input type="text" name="slug" id="slug" class="form-control" value="<?= esc(old('slug', $post !== null ? $post['slug'] : '')) ?>" required>
    </div>
    <div class="mb-3">
        <?php $postLocale = old('locale', $post !== null ? (string) ($post['locale'] ?? 'fr') : 'fr'); ?>
        <?php if (! in_array($postLocale, ['fr', 'en'], true)) {
            $postLocale = 'fr';
        } ?>
        <label class="form-label" for="locale"><?= esc(lang('Admin.form_label_locale')) ?></label>
        <select name="locale" id="locale" class="form-select" style="max-width:16rem">
            <option value="fr" <?= $postLocale === 'fr' ? 'selected' : '' ?>>Français</option>
            <option value="en" <?= $postLocale === 'en' ? 'selected' : '' ?>>English</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="translation_group">Groupe de traduction</label>
        <input type="text" name="translation_group" id="translation_group" class="form-control" maxlength="64" value="<?= esc(old('translation_group', $post !== null ? (string) ($post['translation_group'] ?? '') : '')) ?>" autocomplete="off">
        <div class="form-text">Optionnel — pour relier deux articles FR/EN entre eux.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="title">Titre</label>
        <input type="text" name="title" id="title" class="form-control" value="<?= esc(old('title', $post !== null ? $post['title'] : '')) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="excerpt">Chapô</label>
        <textarea name="excerpt" id="excerpt" class="form-control" rows="2"><?= esc(old('excerpt', $post !== null ? ($post['excerpt'] ?? '') : '')) ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="body_html">Corps de l’article</label>
        <textarea name="body_html" id="body_html" class="form-control" rows="14"><?= old('body_html', $post !== null ? $post['body_html'] : '') ?></textarea>
        <div class="form-text">Éditeur visuel : bouton <strong>Blocs</strong> pour les modèles ; menu <strong>Formats</strong> pour sur-titre / chapô. À l’ouverture de <strong>Code source</strong>, le HTML est automatiquement indenté pour lecture. <strong>Alt</strong> obligatoire sur les images.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="status">Statut</label>
        <select name="status" id="status" class="form-select">
            <?php $st = old('status', $post !== null ? $post['status'] : 'draft'); ?>
            <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>>Brouillon</option>
            <option value="published" <?= $st === 'published' ? 'selected' : '' ?>>Publié</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="published_at">Date de publication (si publié)</label>
        <input type="datetime-local" name="published_at" id="published_at" class="form-control" value="<?= esc($paValue) ?>"<?= $paUtcAttr ?>>
    </div>
    <div class="mb-3">
        <label class="form-label" for="meta_title">Meta title</label>
        <input type="text" name="meta_title" id="meta_title" class="form-control" value="<?= esc(old('meta_title', $post !== null ? ($post['meta_title'] ?? '') : '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="meta_description">Meta description</label>
        <textarea name="meta_description" id="meta_description" class="form-control" rows="2"><?= esc(old('meta_description', $post !== null ? ($post['meta_description'] ?? '') : '')) ?></textarea>
    </div>

    <div class="admin-form-actions">
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= esc(lang('Admin.action_save')) ?></button>
            <a href="<?= site_url('admin/posts') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
        </div>
    </div>
</form>
