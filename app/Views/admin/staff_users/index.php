<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $users */
/** @var array<string, string> $roles */
/** @var \CodeIgniter\Pager\Pager $pager */
?>
<h1 class="h3 mb-1">Équipe</h1>
<p class="text-muted small mb-3">Comptes d’accès au back-office. Seuls les administrateurs voient cet écran.</p>

<div class="mb-3">
    <a href="<?= site_url('admin/staff-users/create') ?>" class="btn btn-primary btn-sm">Inviter un compte</a>
</div>

<?php if ($users === []) : ?>
    <div class="admin-empty">
        <p class="mb-0 text-muted">Aucun compte.</p>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th>E-mail</th>
            <th>Rôle</th>
            <th>État</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u) :
        $rid = (string) ($u['role'] ?? '');
        $roleLabel = $roles[$rid] ?? $rid;
        $active = (int) ($u['is_active'] ?? 1) === 1;
        ?>
        <tr>
            <td><?= esc((string) ($u['email'] ?? '')) ?></td>
            <td><span class="badge text-bg-secondary"><?= esc($roleLabel) ?></span></td>
            <td>
                <?php if ($active) : ?>
                    <span class="badge text-bg-success">Actif</span>
                <?php else : ?>
                    <span class="badge text-bg-secondary">Désactivé</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <a href="<?= site_url('admin/staff-users/edit/' . (int) ($u['id'] ?? 0)) ?>" class="btn btn-outline-primary btn-sm">Modifier</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
    <div><?= (int) $pager->getTotal('default') ?> compte(s)</div>
    <?php if ($pager->getPageCount('default') > 1) : ?>
        <?= $pager->links('default', 'bootstrap_full') ?>
    <?php endif; ?>
</div>
<?php endif; ?>
