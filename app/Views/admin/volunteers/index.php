<?php

declare(strict_types=1);

helper('admin');

use App\Controllers\Front\Join;

/** @var list<array<string, mixed>> $rows */
/** @var string $volunteerFilter */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */

$isStaffAdmin = session()->get('staff_role') === 'admin';
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_volunteers')) ?></h1>
<p class="text-muted small mb-3">Demandes envoyées depuis le formulaire <strong>Rejoindre</strong> du site public.</p>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <form method="get" action="<?= site_url('admin/volunteers') ?>" class="d-flex align-items-center gap-2">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <label class="small text-muted mb-0" for="vol-filter">Afficher</label>
        <select name="status" id="vol-filter" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <option value="" <?= $volunteerFilter === 'all' ? 'selected' : '' ?>>Toutes</option>
            <option value="new" <?= $volunteerFilter === 'new' ? 'selected' : '' ?>>Nouvelles</option>
            <option value="reviewed" <?= $volunteerFilter === 'reviewed' ? 'selected' : '' ?>>Traitées</option>
        </select>
    </form>
    <?php if ($isStaffAdmin && $rows !== []) : ?>
        <form method="post" action="<?= site_url('admin/volunteers/clear-table') ?>" class="ms-md-auto"
              onsubmit="return confirm('Supprimer toutes les candidatures volontaires ? Cette action est irréversible.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">Vider la table</button>
        </form>
    <?php endif; ?>
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
        <th scope="col"><?= admin_list_sort_th('created_at', 'Date', $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('full_name', 'Nom', $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('email', 'E-mail', $sort, $dir) ?></th>
        <th scope="col">Téléphone</th>
        <th scope="col"><?= admin_list_sort_th('status', 'Statut', $sort, $dir) ?></th>
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
            <td class="small text-nowrap"><?= admin_format_datetime($row['created_at'] ?? null) ?></td>
            <td><?= esc((string) ($row['full_name'] ?? '')) ?></td>
            <td><a href="mailto:<?= esc((string) ($row['email'] ?? '')) ?>" class="text-break"><?= esc((string) ($row['email'] ?? '')) ?></a></td>
            <td class="text-nowrap">
                <?php $phone = trim((string) ($row['phone'] ?? '')); ?>
                <?= $phone !== '' ? esc($phone) : '<span class="text-muted small">—</span>' ?>
            </td>
            <td>
                <?php if ($status === 'new') : ?>
                    <span class="badge text-bg-primary">Nouvelle</span>
                <?php elseif ($status === 'reviewed') : ?>
                    <span class="badge text-bg-success">Traitée</span>
                <?php else : ?>
                    <span class="badge text-bg-secondary"><?= esc($status) ?></span>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#volDetailModal" data-vol-id="<?= $id ?>">Détail</button>
                <?php if ($status !== 'reviewed') : ?>
                    <form action="<?= site_url('admin/volunteers/status/' . $id) ?>" method="post" class="d-inline ms-1">
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

<?php foreach ($rows as $row) :
    $id = (int) ($row['id'] ?? 0);
    $msg = (string) ($row['message'] ?? '');
    $sectorRaw = (string) ($row['sector'] ?? '');
    $sectorKeys = Join::normalizeSectorKeys(explode(',', $sectorRaw));
    ?>
<div id="vol-detail-<?= $id ?>" class="d-none">
    <dl class="row mb-0">
        <dt class="col-sm-3">Secteur(s)</dt>
        <dd class="col-sm-9 mb-3">
            <?php if ($sectorKeys !== []) : ?>
                <ul class="mb-0 ps-3">
                    <?php foreach (Join::sectorLabelLines($sectorKeys) as $sectorLine) : ?>
                    <li><?= esc($sectorLine) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif ($sectorRaw !== '') : ?>
                <?= esc($sectorRaw) ?>
            <?php else : ?>
                <span class="text-muted">—</span>
            <?php endif; ?>
        </dd>
        <dt class="col-sm-3">Message</dt>
        <dd class="col-sm-9 mb-0">
            <?php if ($msg !== '') : ?>
                <div class="small border rounded p-2 bg-light"><?= nl2br(esc($msg)) ?></div>
            <?php else : ?>
                <span class="text-muted">—</span>
            <?php endif; ?>
        </dd>
    </dl>
</div>
<?php endforeach; ?>

<div class="modal fade" id="volDetailModal" tabindex="-1" aria-labelledby="volDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="volDetailModalLabel">Détail candidature</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="volDetailModalBody"></div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('volDetailModal');
    if (!modal) {
        return;
    }
    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        var id = btn && btn.getAttribute('data-vol-id');
        var src = id ? document.getElementById('vol-detail-' + id) : null;
        var body = document.getElementById('volDetailModalBody');
        if (body) {
            body.innerHTML = src ? src.innerHTML : '<p class="text-muted mb-0">Contenu introuvable.</p>';
        }
    });
})();
</script>

<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => 'résultat(s)']) ?>
<?php endif; ?>
