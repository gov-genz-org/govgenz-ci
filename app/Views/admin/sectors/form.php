<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $sector */
/** @var int|null $nextOrder */

$isEdit = $sector !== null;
$action = $isEdit
    ? site_url('admin/sectors/update/' . (int) ($sector['id'] ?? 0))
    : site_url('admin/sectors/store');

$code = old('code', $isEdit ? (string) ($sector['code'] ?? '') : '');
$labelFr = old('label_fr', $isEdit ? (string) ($sector['label_fr'] ?? '') : '');
$labelEn = old('label_en', $isEdit ? (string) ($sector['label_en'] ?? '') : '');
$email = old('contact_email', $isEdit ? (string) ($sector['contact_email'] ?? '') : '');
$sort = old('sort_order', $isEdit ? (string) (int) ($sector['sort_order'] ?? 0) : (string) ($nextOrder ?? 10));
$active = old('is_active', $isEdit ? (string) ((int) ($sector['is_active'] ?? 1)) : '1');
?>
<h1 class="h3 mb-1"><?= $isEdit ? 'Modifier un secteur' : 'Nouveau secteur' ?></h1>
<p class="text-muted small mb-3">
    <strong>FR</strong> = texte affiché quand la langue du site est le français ;
    <strong>EN</strong> = texte when the site locale is English (including <code>/en/…</code>).
    Le <strong>code</strong> sert aux formulaires et à la base (volontaires, projets) : il est figé après création.
</p>

<form method="post" action="<?= esc($action, 'attr') ?>" class="border rounded bg-white shadow-sm p-3 p-md-4" accept-charset="UTF-8">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="sec-code" class="form-label">Code technique <span class="text-danger">*</span></label>
            <input type="text" name="code" id="sec-code" class="form-control font-monospace<?= $isEdit ? ' bg-light' : '' ?>"
                   <?= $isEdit ? 'readonly' : 'required' ?> maxlength="32"
                   pattern="[a-z][a-z0-9_-]{0,30}"
                   title="ex. legal, digital"
                   value="<?= esc($code) ?>">
            <?php if (! $isEdit) : ?>
                <div class="form-text">Minuscules, chiffres, tirets ; ex. <code>legal</code>, <code>education</code>.</div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="sec-sort" class="form-label">Ordre d’affichage</label>
            <input type="number" name="sort_order" id="sec-sort" class="form-control" min="0" max="32767" step="1" value="<?= esc($sort) ?>">
        </div>
        <div class="col-md-4">
            <label for="sec-active" class="form-label">Actif sur le site</label>
            <select name="is_active" id="sec-active" class="form-select">
                <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Oui (liste publique, Join, projets)</option>
                <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Non (masqué des listes)</option>
            </select>
        </div>
        <div class="col-12">
            <label for="sec-lab-fr" class="form-label">Libellé — français <span class="text-danger">*</span></label>
            <input type="text" name="label_fr" id="sec-lab-fr" class="form-control" required maxlength="255" value="<?= esc($labelFr) ?>">
        </div>
        <div class="col-12">
            <label for="sec-lab-en" class="form-label">Label — English <span class="text-danger">*</span></label>
            <input type="text" name="label_en" id="sec-lab-en" class="form-control" required maxlength="255" value="<?= esc($labelEn) ?>">
        </div>
        <div class="col-12">
            <label for="sec-mail" class="form-label">E-mail de contact (tuile Secteurs) <span class="text-danger">*</span></label>
            <input type="email" name="contact_email" id="sec-mail" class="form-control" required maxlength="190" value="<?= esc($email) ?>">
        </div>
    </div>

    <div class="alert alert-light border small mt-4 mb-0">
        <strong>CMS (mode source)</strong> — même grille que la table <code>sectors</code> :<br>
        <code class="user-select-all">&lt;div data-gg-cms="sectors-tile-grid"&gt;&lt;/div&gt;</code>
        &nbsp;·&nbsp;
        <code class="user-select-all">&lt;div data-gg-cms="secteurs-tile-grid"&gt;&lt;/div&gt;</code><br>
        Commentaires équivalents : <code>&lt;!-- GG_CMS_SECTORS_TILE_GRID --&gt;</code> ·
        <code>&lt;!-- GG_CMS_SECTEURS_TILE_GRID --&gt;</code>
    </div>

    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a class="btn btn-outline-secondary" href="<?= site_url('admin/sectors') ?>">Annuler</a>
    </div>
</form>
