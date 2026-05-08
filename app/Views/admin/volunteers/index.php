<?php

declare(strict_types=1);

use App\Controllers\Front\Join;

$labels = Join::sectorLabels();

/** @var list<array<string, mixed>> $rows */
/** @var string $volunteerFilter */
/** @var \CodeIgniter\Pager\Pager $pager */
?>
<h1 class="h3 mb-1">Candidatures volontaires</h1>
<p class="text-muted small mb-3">Demandes envoyées depuis le formulaire <strong>Rejoindre</strong> du site public.</p>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <form method="get" action="<?= site_url('admin/volunteers') ?>" class="d-flex align-items-center gap-2">
        <label class="small text-muted mb-0" for="vol-filter">Afficher</label>
        <select name="status" id="vol-filter" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <option value="" <?= $volunteerFilter === 'all' ? 'selected' : '' ?>>Toutes</option>
            <option value="new" <?= $volunteerFilter === 'new' ? 'selected' : '' ?>>Nouvelles</option>
            <option value="reviewed" <?= $volunteerFilter === 'reviewed' ? 'selected' : '' ?>>Traitées</option>
        </select>
    </form>
</div>

<?php if ($rows === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= $volunteerFilter === 'all' ? 'Aucune candidature pour le moment.' : 'Aucune candidature pour ce filtre.' ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <?php if ($volunteerFilter !== 'all') : ?>
                <a href="<?= site_url('admin/volunteers') ?>" class="btn btn-outline-secondary btn-sm">Voir toutes</a>
            <?php endif; ?>
            <a href="<?= site_url('join') ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Formulaire public « Rejoindre »</a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light">
    <tr>
        <th scope="col">Date</th>
        <th scope="col">Nom</th>
        <th scope="col">E-mail</th>
        <th scope="col">Secteur</th>
        <th scope="col">Statut</th>
        <th scope="col">Message</th>
        <th scope="col" class="text-end">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row) :
        $id = (int) ($row['id'] ?? 0);
        $status = (string) ($row['status'] ?? '');
        $msg    = (string) ($row['message'] ?? '');
        ?>
        <tr id="vol-row-<?= $id ?>">
            <td class="small text-nowrap"><?= esc((string) ($row['created_at'] ?? '')) ?></td>
            <td><?= esc((string) ($row['full_name'] ?? '')) ?></td>
            <td><a href="mailto:<?= esc((string) ($row['email'] ?? '')) ?>" class="text-break"><?= esc((string) ($row['email'] ?? '')) ?></a></td>
            <td><?= esc($labels[$row['sector'] ?? ''] ?? (string) ($row['sector'] ?? '')) ?></td>
            <td>
                <?php if ($status === 'new') : ?>
                    <span class="badge text-bg-primary">Nouvelle</span>
                <?php elseif ($status === 'reviewed') : ?>
                    <span class="badge text-bg-success">Traitée</span>
                <?php else : ?>
                    <span class="badge text-bg-secondary"><?= esc($status) ?></span>
                <?php endif; ?>
            </td>
            <td style="max-width: 14rem;">
                <?php if ($msg === '') : ?>
                    <span class="text-muted small">—</span>
                <?php else : ?>
                    <span class="small d-block text-truncate" title="<?= esc($msg) ?>"><?= esc(mb_strimwidth($msg, 0, 90, '…')) ?></span>
                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-bs-toggle="collapse" data-bs-target="#vol-msg-<?= $id ?>" aria-expanded="false">Lire tout</button>
                    <div class="collapse mt-1" id="vol-msg-<?= $id ?>">
                        <div class="small border rounded p-2 bg-light"><?= nl2br(esc($msg)) ?></div>
                    </div>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <?php if ($status !== 'reviewed') : ?>
                    <form action="<?= site_url('admin/volunteers/status/' . $id) ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="reviewed">
                        <button type="submit" class="btn btn-outline-success btn-sm">Marquer traitée</button>
                    </form>
                <?php endif; ?>
                <?php if ($status !== 'new') : ?>
                    <form action="<?= site_url('admin/volunteers/status/' . $id) ?>" method="post" class="d-inline ms-1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="new">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Remettre nouvelle</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
    <div><?= (int) $pager->getTotal('default') ?> résultat(s)</div>
    <?php if ($pager->getPageCount('default') > 1) : ?>
        <?= $pager->links('default', 'bootstrap_full') ?>
    <?php endif; ?>
</div>
<?php endif; ?>
