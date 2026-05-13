<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $events */
/** @var string $filterOutcome */
/** @var string $searchQuery */
/** @var \CodeIgniter\Pager\Pager $pager */

$queryParams = array_filter([
    'outcome' => $filterOutcome !== 'all' ? $filterOutcome : null,
    'q'       => $searchQuery !== '' ? $searchQuery : null,
]);
$exportUrl = site_url('admin/login-events/export');
if ($queryParams !== []) {
    $exportUrl .= '?' . http_build_query($queryParams);
}
?>
<h1 class="h3 mb-1">Journal de connexion</h1>
<p class="text-muted small mb-3">Tentatives sur le formulaire de connexion rédaction (succès, échecs, limitation).</p>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= esc($exportUrl) ?>" class="btn btn-outline-secondary btn-sm">Exporter CSV</a>
    <form method="post" action="<?= site_url('admin/login-events/clear-table') ?>"
          onsubmit="return confirm('Vider tout le journal de connexion ? Cette action est irréversible.');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm">Vider la table</button>
    </form>
    <form method="get" action="<?= site_url('admin/login-events') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <div>
            <label class="small text-muted mb-0 d-block" for="le-search-q">E-mail (fragment)</label>
            <input type="search" name="q" id="le-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="Rechercher…" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="le-outcome">Résultat</label>
            <select name="outcome" id="le-outcome" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterOutcome === 'all' ? 'selected' : '' ?>>Tous</option>
                <option value="success" <?= $filterOutcome === 'success' ? 'selected' : '' ?>>Succès</option>
                <option value="failure" <?= $filterOutcome === 'failure' ? 'selected' : '' ?>>Échec</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filtrer</button>
    </form>
</div>

<?php if ($events === []) : ?>
    <div class="admin-empty">
        <p class="mb-0 text-muted">Aucun événement pour ce filtre.</p>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0 small">
    <thead class="table-light">
        <tr>
            <th>Date</th>
            <th>Résultat</th>
            <th>Détail</th>
            <th>E-mail</th>
            <th>ID staff</th>
            <th>IP</th>
            <th>Navigateur</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $ev) :
        $ua = (string) ($ev['user_agent'] ?? '');
        $uaShort = mb_strlen($ua) > 72 ? mb_substr($ua, 0, 69) . '…' : $ua;
        ?>
        <tr>
            <td class="text-nowrap"><?= esc((string) ($ev['created_at'] ?? '')) ?></td>
            <td><span class="badge <?= (($ev['outcome'] ?? '') === 'success') ? 'text-bg-success' : 'text-bg-warning' ?>"><?= esc((string) ($ev['outcome'] ?? '')) ?></span></td>
            <td><?= esc((string) ($ev['detail'] ?? '')) ?></td>
            <td><?= esc((string) ($ev['email_attempt'] ?? '')) ?></td>
            <td><?= esc((string) ($ev['staff_user_id'] ?? '')) ?></td>
            <td class="text-nowrap"><code><?= esc((string) ($ev['ip_address'] ?? '')) ?></code></td>
            <td title="<?= esc($ua, 'attr') ?>"><?= esc($uaShort) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
    <div><?= (int) $pager->getTotal('default') ?> événement(s)</div>
    <?php if ($pager->getPageCount('default') > 1) : ?>
        <?= $pager->links('default', 'bootstrap_full') ?>
    <?php endif; ?>
</div>
<?php endif; ?>
