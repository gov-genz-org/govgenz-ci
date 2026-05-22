<?php

declare(strict_types=1);

/** @var bool $loggedOutBanner */
/** @var string $loginPasswordHint */

$host = strtolower((string) service('request')->getUri()->getHost());
$isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
?>
<div class="admin-login-card mx-auto">
    <div class="card shadow border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="fw-bold text-dark fs-5 mb-1">GovGenZ</div>
                <h1 class="h5 text-secondary mb-0 fw-normal"><?= esc(lang('Admin.auth_login_title')) ?></h1>
                <p class="small text-secondary mb-0 mt-2">Accès réservé au personnel autorisé.</p>
            </div>

            <?php if (! empty($loggedOutBanner)) : ?>
                <div class="alert alert-success py-2 small mb-3" role="status">
                    Vous avez bien été déconnecté.
                </div>
            <?php endif; ?>

            <form action="<?= site_url('admin/login') ?>" method="post" accept-charset="UTF-8">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-secondary">Adresse e-mail</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg" value="<?= esc(old('email')) ?>" required autofocus autocomplete="username" placeholder="vous@exemple.org">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label small fw-semibold text-secondary">Mot de passe</label>
                    <div class="input-group input-group-lg">
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password" placeholder="••••••••" aria-describedby="pw-toggle-help">
                        <button type="button" class="btn btn-outline-secondary px-2 py-0 d-inline-flex align-items-center justify-content-center admin-login-pw-toggle" style="min-width: 2.75rem;" id="admin-login-toggle-password" aria-pressed="false" aria-label="Afficher le mot de passe" title="Afficher le mot de passe"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                    </div>
                    <p id="pw-toggle-help" class="form-text small mb-0"><?= esc($loginPasswordHint) ?></p>
                </div>
                <button type="submit" class="btn btn-dark btn-lg w-100">Se connecter</button>
            </form>
            <?php if (ENVIRONMENT === 'development' && $isLocalHost) : ?>
                <details class="mt-4 small text-muted">
                    <summary class="text-secondary" style="cursor: pointer;">Compte de développement</summary>
                    <p class="mb-0 mt-2"><code>admin@govgenz.local</code> / <code>changeme</code></p>
                </details>
            <?php endif; ?>
        </div>
    </div>
    <p class="text-center small text-muted mt-4 mb-0">
        <a href="<?= site_url('/') ?>" class="text-decoration-none text-secondary">← Voir le site</a>
    </p>
</div>
<script defer src="<?= base_url('js/admin/login-password-toggle.js') ?>"></script>
