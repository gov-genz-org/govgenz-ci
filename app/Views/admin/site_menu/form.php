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
<p class="text-muted small mb-3">Le <strong>surlignage</strong> détermine quel lien est marqué « actif » selon la page ouverte : <code>home</code>, <code>press</code>, <code>join</code>, <code>contact</code>, <code>admin_login</code>, ou le slug d’une page CMS pour les URLs simples. Pour un lien externe sans surlignage, utilisez <code>none</code>.</p>

<form action="<?= esc($action, 'attr') ?>" method="post" accept-charset="UTF-8">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label" for="label">Libellé</label>
        <input type="text" name="label" id="label" class="form-control" required maxlength="255" value="<?= esc(old('label', $item !== null ? (string) ($item['label'] ?? '') : '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="locale">Langue du menu</label>
        <select name="locale" id="locale" class="form-select" style="max-width:16rem">
            <option value="fr" <?= $localeSel === 'fr' ? 'selected' : '' ?>>Français (URLs sans préfixe)</option>
            <option value="en" <?= $localeSel === 'en' ? 'selected' : '' ?>>English (/en/…)</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="sort_order">Ordre (tri croissant)</label>
        <input type="number" name="sort_order" id="sort_order" class="form-control" style="max-width:12rem" min="0" step="1" value="<?= esc($sort) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="href_kind">Type de lien</label>
        <select name="href_kind" id="href_kind" class="form-select" style="max-width:28rem">
            <option value="home" <?= $kind === 'home' ? 'selected' : '' ?>>Accueil (racine du site)</option>
            <option value="segment" <?= $kind === 'segment' ? 'selected' : '' ?>>Segment interne (une partie d’URL, ex. qui-sommes-nous)</option>
            <option value="path" <?= $kind === 'path' ? 'selected' : '' ?>>Chemin interne (plusieurs segments, ex. admin/login)</option>
            <option value="external" <?= $kind === 'external' ? 'selected' : '' ?>>URL externe (https://…)</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="href_target">Cible</label>
        <input type="text" name="href_target" id="href_target" class="form-control" maxlength="512" value="<?= esc($target) ?>" placeholder="Vide pour « Accueil », sinon slug, chemin ou URL complète">
        <div class="form-text">Pour « Accueil », laissez vide. Pour Presse / Contact, un seul segment (press, contact…).</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="match_key">Clé de surlignage</label>
        <input type="text" name="match_key" id="match_key" class="form-control" required maxlength="190" pattern="[a-z0-9_-]+" value="<?= esc($mk) ?>">
        <div class="form-text">Exemples : <code>home</code>, <code>qui-sommes-nous</code>, <code>admin_login</code>, <code>none</code>.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="css_class">Classes CSS (optionnel)</label>
        <input type="text" name="css_class" id="css_class" class="form-control" maxlength="80" value="<?= esc($cssClass) ?>" placeholder="Ex. ggz-nav-admin">
        <div class="form-text">Les liens visibles sont centrés dans la barre. <code>ggz-nav-end</code> n’est plus utilisé pour la mise en page.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="is_active">Visible dans le menu</label>
        <select name="is_active" id="is_active" class="form-select" style="max-width:16rem">
            <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Oui</option>
            <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Non</option>
        </select>
    </div>
    <div class="admin-form-actions d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= $item ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= site_url('admin/site-menu') ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
