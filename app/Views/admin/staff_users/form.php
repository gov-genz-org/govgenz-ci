<?php

declare(strict_types=1);

use App\Libraries\StaffAuthPolicy;

/** @var array<string, mixed>|null $user */
/** @var array<string, string> $roles */
/** @var bool $is_edit */

$pwMin = StaffAuthPolicy::loginPasswordMinLength();
$id = $is_edit ? (int) ($user['id'] ?? 0) : 0;
?>
<h1 class="h3 mb-2"><?= $is_edit ? 'Modifier le compte' : 'Inviter un compte' ?></h1>
<p class="text-muted small mb-4">
    <?php if ($is_edit) : ?>
        Laisser le mot de passe vide pour ne pas le changer. Minimum <?= (int) $pwMin ?> caractères si renseigné.
    <?php else : ?>
        Mot de passe initial : au moins <?= (int) $pwMin ?> caractères. Le collaborateur pourra le modifier après connexion si vous ajoutez ce flux plus tard.
    <?php endif; ?>
</p>

<form action="<?= $is_edit ? site_url('admin/staff-users/update/' . $id) : site_url('admin/staff-users/store') ?>" method="post" accept-charset="UTF-8" class="card shadow-sm border-0">
    <?= csrf_field() ?>
    <div class="card-body">
        <?php if ($is_edit) : ?>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="text" class="form-control" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled autocomplete="off">
                <p class="form-text mb-0">L’e-mail ne peut pas être modifié ici (cohérence des journaux). Créez un autre compte si besoin.</p>
            </div>
        <?php else : ?>
            <div class="mb-3">
                <label class="form-label" for="su-email">E-mail</label>
                <input type="email" name="email" id="su-email" class="form-control" value="<?= esc(old('email')) ?>" required maxlength="190" autocomplete="username">
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label" for="su-password"><?= $is_edit ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe' ?></label>
            <input type="password" name="password" id="su-password" class="form-control" <?= $is_edit ? '' : 'required minlength="' . (int) $pwMin . '"' ?> autocomplete="new-password">
        </div>

        <div class="mb-3">
            <label class="form-label" for="su-role">Rôle</label>
            <select name="role" id="su-role" class="form-select" required>
                <?php foreach ($roles as $value => $label) : ?>
                    <option value="<?= esc($value, 'attr') ?>" <?= (string) old('role', $is_edit ? (string) ($user['role'] ?? '') : '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-text mb-0"><strong>Administrateur</strong> : journal de connexion, gestion de l’équipe. <strong>Éditeur</strong> : contenu (pages, presse, médias, volontaires).</p>
        </div>

        <?php if ($is_edit) : ?>
            <div class="mb-3">
                <label class="form-label" for="su-active">État du compte</label>
                <select name="is_active" id="su-active" class="form-select" required>
                    <option value="1" <?= (string) old('is_active', (string) (int) ($user['is_active'] ?? 1)) === '1' ? 'selected' : '' ?>>Actif</option>
                    <option value="0" <?= (string) old('is_active', (string) (int) ($user['is_active'] ?? 1)) === '0' ? 'selected' : '' ?>>Désactivé</option>
                </select>
            </div>
        <?php endif; ?>

        <div class="admin-form-actions d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-dark"><?= $is_edit ? 'Enregistrer' : 'Créer le compte' ?></button>
            <a href="<?= site_url('admin/staff-users') ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
