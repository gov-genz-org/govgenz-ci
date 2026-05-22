<?php

declare(strict_types=1);

helper(['admin', 'project']);

use App\Models\ProjectContributionModel;

/** @var list<array<string, mixed>> $rows */
/** @var string $filter */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */

$isStaffAdmin = session()->get('staff_role') === 'admin';

$typeLabels = [
    ProjectContributionModel::TYPE_BUDGET   => 'Financement (budget)',
    ProjectContributionModel::TYPE_MATERIAL => 'Apport matériel',
];
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_contributions')) ?></h1>
<p class="text-muted small mb-3">Demandes envoyées depuis le formulaire <strong>Financer ce projet</strong> sur les fiches publiées. Une proposition <strong>validée</strong> apparaît sur la fiche FR et EN du projet (nom et type de soutien, sans coordonnées).</p>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <form method="get" action="<?= site_url('admin/project-contributions') ?>" class="d-flex align-items-center gap-2">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <label class="small text-muted mb-0" for="contrib-filter">Afficher</label>
        <select name="status" id="contrib-filter" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <option value="" <?= $filter === 'all' ? 'selected' : '' ?>>Toutes</option>
            <option value="new" <?= $filter === 'new' ? 'selected' : '' ?>>Nouvelles</option>
            <option value="reviewed" <?= $filter === 'reviewed' ? 'selected' : '' ?>>Validées</option>
            <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>Refusées</option>
        </select>
    </form>
    <?php if ($isStaffAdmin && $rows !== []) : ?>
        <form method="post" action="<?= site_url('admin/project-contributions/clear-table') ?>" class="ms-md-auto"
              onsubmit="return confirm('Supprimer toutes les propositions ? Cette action est irréversible.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">Vider la table</button>
        </form>
    <?php endif; ?>
</div>

<?php if ($rows === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= $filter === 'all' ? 'Aucune proposition pour le moment.' : 'Aucune proposition pour ce filtre.' ?></p>
        <?php if ($filter !== 'all') : ?>
            <a href="<?= site_url('admin/project-contributions') ?>" class="btn btn-outline-secondary btn-sm">Voir toutes</a>
        <?php endif; ?>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light">
    <tr>
        <th scope="col"><?= admin_list_sort_th('created_at', 'Date', $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('contribution_type', 'Type', $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('project_title', 'Projet', $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('donor_name', 'Nom', $sort, $dir) ?></th>
        <th scope="col">Contact</th>
        <th scope="col"><?= admin_list_sort_th('status', 'Statut', $sort, $dir) ?></th>
        <th scope="col" class="text-end">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row) :
        $id = (int) ($row['id'] ?? 0);
        $status = (string) ($row['status'] ?? '');
        $ctype = (string) ($row['contribution_type'] ?? '');
        $slug = (string) ($row['project_slug'] ?? '');
        ?>
        <tr id="contrib-row-<?= $id ?>">
            <td class="small text-nowrap"><?= admin_format_datetime($row['created_at'] ?? null) ?></td>
            <td class="small"><?= esc($typeLabels[$ctype] ?? $ctype) ?></td>
            <td>
                <?php if ($slug !== '') : ?>
                    <a href="<?= esc(project_public_url($slug), 'attr') ?>" target="_blank" rel="noopener"><?= esc((string) ($row['project_title'] ?? $slug)) ?></a>
                <?php else : ?>
                    <?= esc((string) ($row['project_title'] ?? '')) ?>
                <?php endif; ?>
            </td>
            <td><?= esc((string) ($row['donor_name'] ?? '')) ?></td>
            <td class="text-break small">
                <?php
                $donorMail = trim((string) ($row['donor_email'] ?? ''));
                $donorPhone = trim((string) ($row['contact'] ?? ''));
                if ($donorMail !== '') : ?>
                    <a href="mailto:<?= esc($donorMail, 'attr') ?>"><?= esc($donorMail) ?></a>
                <?php endif; ?>
                <?php if ($donorMail !== '' && $donorPhone !== '') : ?><br><?php endif; ?>
                <?php if ($donorPhone !== '') : ?>
                    <?= esc($donorPhone) ?>
                <?php endif; ?>
                <?php if ($donorMail === '' && $donorPhone === '') : ?>—<?php endif; ?>
            </td>
            <td>
                <?php if ($status === ProjectContributionModel::STATUS_NEW) : ?>
                    <span class="badge text-bg-primary">Nouvelle</span>
                <?php elseif ($status === ProjectContributionModel::STATUS_REVIEWED) : ?>
                    <span class="badge text-bg-success">Validée (publiée)</span>
                <?php elseif ($status === ProjectContributionModel::STATUS_REJECTED) : ?>
                    <span class="badge text-bg-secondary">Refusée</span>
                <?php else : ?>
                    <span class="badge text-bg-secondary"><?= esc($status) ?></span>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#contribDetailModal" data-contrib-id="<?= $id ?>">Détail</button>
                <?php if ($status !== ProjectContributionModel::STATUS_REVIEWED) : ?>
                    <form action="<?= site_url('admin/project-contributions/status/' . $id) ?>" method="post" class="d-inline ms-1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="reviewed">
                        <button type="submit" class="btn btn-outline-success btn-sm" title="Affiche la proposition sur la fiche projet (sans contact)">Valider et publier</button>
                    </form>
                <?php endif; ?>
                <?php if ($status !== ProjectContributionModel::STATUS_REJECTED) : ?>
                    <form action="<?= site_url('admin/project-contributions/status/' . $id) ?>" method="post" class="d-inline ms-1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Refuser</button>
                    </form>
                <?php endif; ?>
                <?php if ($status !== ProjectContributionModel::STATUS_NEW) : ?>
                    <form action="<?= site_url('admin/project-contributions/status/' . $id) ?>" method="post" class="d-inline ms-1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="new">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Nouvelle</button>
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
    $ctype = (string) ($row['contribution_type'] ?? '');
    ?>
<div id="contrib-detail-<?= $id ?>" class="d-none">
    <dl class="row mb-0 small">
        <dt class="col-sm-4">Type</dt>
        <dd class="col-sm-8 mb-2"><?= esc($typeLabels[$ctype] ?? $ctype) ?></dd>
        <dt class="col-sm-4">Projet</dt>
        <dd class="col-sm-8 mb-2"><?= esc((string) ($row['project_title'] ?? '')) ?> <span class="text-muted">(<?= esc((string) ($row['project_slug'] ?? '')) ?>)</span></dd>
        <dt class="col-sm-4">Locale</dt>
        <dd class="col-sm-8 mb-2"><?= esc((string) ($row['locale'] ?? '')) ?></dd>
        <?php if ($ctype === ProjectContributionModel::TYPE_BUDGET) : ?>
            <dt class="col-sm-4">Montant proposé</dt>
            <dd class="col-sm-8 mb-2"><?= esc((string) ($row['amount'] ?? '')) !== '' ? esc((string) $row['amount']) : '—' ?></dd>
        <?php else : ?>
            <dt class="col-sm-4">Article(s)</dt>
            <dd class="col-sm-8 mb-2"><?= nl2br(esc((string) ($row['items'] ?? ''))) ?></dd>
            <dt class="col-sm-4">Quantité</dt>
            <dd class="col-sm-8 mb-2"><?= esc((string) ($row['quantity'] ?? '')) !== '' ? esc((string) $row['quantity']) : '—' ?></dd>
            <dt class="col-sm-4">Disponible à partir du</dt>
            <dd class="col-sm-8 mb-2"><?= esc((string) ($row['available_from'] ?? '')) !== '' ? esc((string) $row['available_from']) : '—' ?></dd>
            <dt class="col-sm-4">Lieu récupération</dt>
            <dd class="col-sm-8 mb-2"><?= esc((string) ($row['pickup_location'] ?? '')) !== '' ? esc((string) $row['pickup_location']) : '—' ?></dd>
            <dt class="col-sm-4">Livraison</dt>
            <dd class="col-sm-8 mb-2">
                <?php
                $del = $row['can_deliver'] ?? null;
                if ($del === null || $del === '') {
                    echo '—';
                } elseif ((string) $del === '1') {
                    echo 'Oui';
                } else {
                    echo 'Non';
                }
                ?>
            </dd>
        <?php endif; ?>
        <dt class="col-sm-4">Remarques</dt>
        <dd class="col-sm-8 mb-0">
            <?php $remarks = trim((string) ($row['remarks'] ?? '')); ?>
            <?= $remarks !== '' ? nl2br(esc($remarks)) : '—' ?>
        </dd>
    </dl>
</div>
<?php endforeach; ?>

<div class="modal fade" id="contribDetailModal" tabindex="-1" aria-labelledby="contribDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="contribDetailModalLabel">Détail proposition</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="contribDetailModalBody"></div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('contribDetailModal');
    if (!modal) {
        return;
    }
    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        var id = btn && btn.getAttribute('data-contrib-id');
        var src = id ? document.getElementById('contrib-detail-' + id) : null;
        var body = document.getElementById('contribDetailModalBody');
        if (body) {
            body.innerHTML = src ? src.innerHTML : '<p class="text-muted mb-0">Contenu introuvable.</p>';
        }
    });
})();
</script>

<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => 'proposition(s)']) ?>
<?php endif; ?>
