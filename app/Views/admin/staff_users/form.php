<?php

declare(strict_types=1);

use App\Libraries\StaffAuthPolicy;
use App\Libraries\StaffInvite;

/** @var array<string, mixed>|null $user */
/** @var array<string, string> $roles */
/** @var bool $is_edit */
/** @var bool $hasNotifyColumn */
$hasNotifyColumn = $hasNotifyColumn ?? false;

$pwMin = StaffAuthPolicy::loginPasswordMinLength();
$id = $is_edit ? (int) ($user['id'] ?? 0) : 0;
$invitePending = $is_edit && $user !== null && StaffInvite::isPending($user);
$inviteExpired = $is_edit && $user !== null && StaffInvite::isExpired($user);
$canResendInvite = $is_edit && $user !== null && StaffInvite::canResendInvite($user);
$inviteExpiryHours = StaffInvite::expiryHours();
$inviteExpiryLabel = $inviteExpiryHours <= 24
    ? lang('Admin.staff_invite_expiry_24h')
    : lang('Admin.staff_invite_expiry_days', [(string) (int) ceil($inviteExpiryHours / 24)]);
?>
<h1 class="h3 mb-2"><?= esc($is_edit ? lang('Admin.form_staff_edit') : lang('Admin.form_staff_invite')) ?></h1>
<p class="text-muted small mb-4">
    <?php if ($is_edit) : ?>
        <?php if ($invitePending) : ?>
            <span class="badge text-bg-warning text-dark"><?= esc(lang('Admin.badge_invite_pending')) ?></span>
            <?= esc(lang('Admin.help_staff_invite_pending')) ?>
        <?php elseif ($inviteExpired) : ?>
            <span class="badge text-bg-danger"><?= esc(lang('Admin.badge_link_expired')) ?></span>
            <?= esc(lang('Admin.help_staff_invite_expired')) ?>
        <?php else : ?>
            <?= esc(lang('Admin.help_staff_password_optional', [(string) (int) $pwMin])) ?>
        <?php endif; ?>
    <?php else : ?>
        <?= esc(lang('Admin.help_staff_invite_create', [$inviteExpiryLabel])) ?>
    <?php endif; ?>
</p>

<?php if ($canResendInvite) : ?>
    <form method="post" action="<?= site_url('admin/staff-users/resend-invite/' . $id) ?>" class="mb-3">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-primary btn-sm"><?= esc(lang('Admin.action_resend_invite')) ?></button>
    </form>
<?php endif; ?>

<form action="<?= $is_edit ? site_url('admin/staff-users/update/' . $id) : site_url('admin/staff-users/store') ?>" method="post" accept-charset="UTF-8" class="card shadow-sm border-0">
    <?= csrf_field() ?>
    <div class="card-body">
        <?php if ($is_edit) : ?>
            <div class="mb-3">
                <label class="form-label"><?= esc(lang('Admin.form_staff_email')) ?></label>
                <input type="text" class="form-control" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled autocomplete="off">
                <p class="form-text mb-0"><?= esc(lang('Admin.form_staff_email_locked_help')) ?></p>
            </div>
        <?php else : ?>
            <div class="mb-3">
                <label class="form-label" for="su-email"><?= esc(lang('Admin.form_staff_email')) ?></label>
                <input type="email" name="email" id="su-email" class="form-control" value="<?= esc(old('email')) ?>" required maxlength="190" autocomplete="username">
            </div>
        <?php endif; ?>

        <?php if ($is_edit && ! $invitePending) : ?>
            <div class="mb-3">
                <label class="form-label" for="su-password"><?= esc(lang('Admin.form_staff_password_new')) ?></label>
                <input type="password" name="password" id="su-password" class="form-control" autocomplete="new-password">
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label" for="su-role"><?= esc(lang('Admin.form_staff_role')) ?></label>
            <select name="role" id="su-role" class="form-select" required>
                <?php foreach ($roles as $value => $label) : ?>
                    <option value="<?= esc($value, 'attr') ?>" <?= (string) old('role', $is_edit ? (string) ($user['role'] ?? '') : '') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="form-text mb-0"><?= lang('Admin.form_staff_role_help') ?></p>
        </div>

        <?php if ($is_edit) : ?>
            <div class="mb-3">
                <label class="form-label" for="su-active"><?= esc(lang('Admin.form_staff_active')) ?></label>
                <select name="is_active" id="su-active" class="form-select" required>
                    <option value="1" <?= (string) old('is_active', (string) (int) ($user['is_active'] ?? 1)) === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_staff_active_on')) ?></option>
                    <option value="0" <?= (string) old('is_active', (string) (int) ($user['is_active'] ?? 1)) === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_staff_active_off')) ?></option>
                </select>
            </div>
            <?php if ($hasNotifyColumn) : ?>
            <div class="mb-3">
                <label class="form-label" for="su-notify"><?= esc(lang('Admin.form_staff_notify')) ?></label>
                <select name="notify_form_submissions" id="su-notify" class="form-select" required>
                    <option value="1" <?= (string) old('notify_form_submissions', (string) (int) ($user['notify_form_submissions'] ?? 1)) === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_staff_notify_on')) ?></option>
                    <option value="0" <?= (string) old('notify_form_submissions', (string) (int) ($user['notify_form_submissions'] ?? 1)) === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_staff_notify_off')) ?></option>
                </select>
                <p class="form-text mb-0"><?= esc(lang('Admin.form_staff_notify_help')) ?></p>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="admin-form-actions d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-dark"><?= esc(lang($is_edit ? 'Admin.action_save' : 'Admin.action_send_invite')) ?></button>
            <a href="<?= site_url('admin/staff-users') ?>" class="btn btn-outline-secondary"><?= esc(lang('Admin.action_cancel')) ?></a>
        </div>
    </div>
</form>
