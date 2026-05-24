<?php

declare(strict_types=1);

/** @var string $token */
/** @var string $email */
/** @var int $passwordMin */
/** @var string $passwordHint */
?>
<div class="admin-login-card mx-auto">
    <div class="card shadow border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="fw-bold text-dark fs-5 mb-1">GovGenZ</div>
                <h1 class="h5 text-secondary mb-0 fw-normal"><?= esc(lang('Admin.auth_invite_setup_title')) ?></h1>
                <p class="small text-secondary mb-0 mt-2"><?= esc(lang('Admin.auth_invite_setup_intro')) ?></p>
            </div>

            <p class="small text-muted mb-3"><?= esc(lang('Admin.auth_account_label')) ?> <strong><?= esc($email) ?></strong></p>

            <form action="<?= site_url('admin/invite/' . esc($token, 'url')) ?>" method="post" accept-charset="UTF-8">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold text-secondary"><?= esc(lang('Admin.auth_password_label')) ?></label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg" required minlength="<?= (int) $passwordMin ?>" autocomplete="new-password" autofocus>
                </div>
                <div class="mb-4">
                    <label for="password_confirm" class="form-label small fw-semibold text-secondary"><?= esc(lang('Admin.auth_password_confirm')) ?></label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control form-control-lg" required minlength="<?= (int) $passwordMin ?>" autocomplete="new-password">
                    <p class="form-text small mb-0"><?= esc($passwordHint) ?></p>
                </div>
                <button type="submit" class="btn btn-dark btn-lg w-100"><?= esc(lang('Admin.auth_save_and_login')) ?></button>
            </form>
        </div>
    </div>
    <p class="text-center small text-muted mt-4 mb-0">
        <a href="<?= site_url('admin/login') ?>" class="text-decoration-none text-secondary"><?= esc(lang('Admin.auth_invite_already_active')) ?></a>
    </p>
</div>
