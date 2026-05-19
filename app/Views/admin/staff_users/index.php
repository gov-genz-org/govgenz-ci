<?php

declare(strict_types=1);

use App\Libraries\StaffInvite;

helper('admin');

/** @var list<array<string, mixed>> $users */
/** @var array<string, string> $roles */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */
?>
<h1 class="h3 mb-1">Équipe</h1>
<p class="text-muted small mb-3">Comptes d’accès au back-office. Seuls les administrateurs voient cet écran.</p>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <a href="<?= site_url('admin/staff-users/create') ?>" class="btn btn-primary btn-sm">Inviter un compte</a>
    <?php if ($users !== []) : ?>
        <form method="post" action="<?= site_url('admin/staff-users/clear-table') ?>" class="ms-md-auto"
              onsubmit="return confirm('Supprimer tous les comptes équipe sauf le vôtre ? Cette action est irréversible.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">Vider la table</button>
        </form>
    <?php endif; ?>
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
            <th><?= admin_list_sort_th('email', 'E-mail', $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('role', 'Rôle', $sort, $dir) ?></th>
            <th>Activation</th>
            <th><?= admin_list_sort_th('is_active', 'État', $sort, $dir) ?></th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u) :
        $rid = (string) ($u['role'] ?? '');
        $roleLabel = $roles[$rid] ?? $rid;
        $active = (int) ($u['is_active'] ?? 1) === 1;
        $uid = (int) ($u['id'] ?? 0);
        $isSelf = $uid === (int) session()->get('staff_user_id');
        $pending = StaffInvite::isPending($u);
        $expired = StaffInvite::isExpired($u);
        ?>
        <tr>
            <td><?= esc((string) ($u['email'] ?? '')) ?></td>
            <td><span class="badge text-bg-secondary"><?= esc($roleLabel) ?></span></td>
            <td>
                <?php if ($pending) : ?>
                    <span class="badge text-bg-warning text-dark">Invitation en attente</span>
                <?php elseif ($expired) : ?>
                    <span class="badge text-bg-danger">Lien expiré</span>
                <?php else : ?>
                    <span class="badge text-bg-success">Activé</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($active) : ?>
                    <span class="badge text-bg-success">Actif</span>
                <?php else : ?>
                    <span class="badge text-bg-secondary">Désactivé</span>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <a href="<?= site_url('admin/staff-users/edit/' . $uid) ?>" class="btn btn-outline-primary btn-sm">Modifier</a>
                <?php if (! $isSelf) : ?>
                    <form method="post" action="<?= site_url('admin/staff-users/delete/' . $uid) ?>" class="d-inline ms-1"
                          onsubmit="return confirm('Supprimer définitivement ce compte ? Cette action est irréversible.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => 'compte(s)']) ?>
<?php endif; ?>
