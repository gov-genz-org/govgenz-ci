<?php

declare(strict_types=1);

use App\Libraries\StaffAuthPolicy;
use App\Libraries\StaffInvite;

/** @var array<string, mixed>|null $user */
/** @var array<string, string> $roles */
/** @var bool $is_edit */

$pwMin = StaffAuthPolicy::loginPasswordMinLength();
$id = $is_edit ? (int) ($user['id'] ?? 0) : 0;
$invitePending = $is_edit && $user !== null && StaffInvite::isPending($user);
$inviteExpiryHours = StaffInvite::expiryHours();
$inviteExpiryLabel = $inviteExpiryHours <= 24
    ? '24 heures'
    : ((int) ceil($inviteExpiryHours / 24) . ' jours');
?>
<h1 class="h3 mb-2"><?= $is_edit ? 'Modifier le compte' : 'Inviter un compte' ?></h1>
<p class="text-muted small mb-4">
    <?php if ($is_edit) : ?>
        <?php if ($invitePending) : ?>
            <span class="badge text-bg-warning text-dark">Invitation en attente</span>
            — le collaborateur doit ouvrir le lien reçu par e-mail pour choisir son mot de passe.
        <?php else : ?>
            Laisser le mot de passe vide pour ne pas le changer. Minimum <?= (int) $pwMin ?> caractères si renseigné.
        <?php endif; ?>
    <?php else : ?>
        Saisissez l’e-mail et le rôle : un e-mail d’activation est envoyé (lien valable <?= esc($inviteExpiryLabel) ?>).
    <?php endif; ?>
</p>

<?php if ($is_edit && $invitePending) : ?>
    <form method="post" action="<?= site_url('admin/staff-users/resend-invite/' . $id) ?>" class="mb-3">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-primary btn-sm">Renvoyer l’e-mail d’invitation</button>
    </form>
<?php endif; ?>

<form action="<?= $is_edit ? site_url('admin/staff-users/update/' . $id) : site_url('admin/staff-users/store') ?>" method="post" accept-charset="UTF-8" class="card shadow-sm border-0">
    <?= csrf_field() ?>
    <div class="card-body">
        <?php if ($is_edit) : ?>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="text" class="form-control" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled autocomplete="off">
                <p class="form-text mb-0">L’e-mail ne peut pas être modifié ici (cohérence des journaux).</p>
            </div>
        <?php else : ?>
            <div class="mb-3">
                <label class="form-label" for="su-email">E-mail</label>
                <input type="email" name="email" id="su-email" class="form-control" value="<?= esc(old('email')) ?>" required maxlength="190" autocomplete="username">
            </div>
        <?php endif; ?>

        <?php if ($is_edit && ! $invitePending) : ?>
            <div class="mb-3">
                <label class="form-label" for="su-password">Nouveau mot de passe (optionnel)</label>
                <input type="password" name="password" id="su-password" class="form-control" autocomplete="new-password">
            </div>
        <?php endif; ?>

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
            <button type="submit" class="btn btn-dark"><?= $is_edit ? 'Enregistrer' : 'Envoyer l’invitation' ?></button>
            <a href="<?= site_url('admin/staff-users') ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</form>
