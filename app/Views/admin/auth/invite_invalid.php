<?php

declare(strict_types=1);
?>
<div class="admin-login-card mx-auto">
    <div class="card shadow border-0 rounded-4 overflow-hidden">
        <div class="card-body p-4 p-sm-5 text-center">
            <h1 class="h5 text-secondary mb-3"><?= esc(lang('Admin.auth_invite_invalid_title')) ?></h1>
            <p class="small text-muted mb-4">
                Ce lien est incorrect, déjà utilisé ou expiré. Demandez à un administrateur de renvoyer l’invitation depuis l’écran Équipe.
            </p>
            <a href="<?= site_url('admin/login') ?>" class="btn btn-dark">Retour à la connexion</a>
        </div>
    </div>
</div>
