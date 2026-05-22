<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $rows */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h3 mb-0"><?= esc(lang('Admin.title_sectors')) ?></h1>
    <a class="btn btn-primary btn-sm" href="<?= site_url('admin/sectors/create') ?>"><?= esc(lang('Admin.breadcrumb_sector_new')) ?></a>
</div>
<p class="text-muted small mb-3">
    Libellés <strong>français</strong> et <strong>anglais</strong> (site public + formulaire Rejoindre + projets).
    Dans le CMS, grille dynamique : <code>data-gg-cms="sectors-tile-grid"</code> (EN) ou
    <code>data-gg-cms="secteurs-tile-grid"</code> (FR) — même rendu.
</p>

<?php if ($rows === []) : ?>
    <p class="text-muted">Aucun secteur — exécutez les migrations ou créez une entrée.</p>
<?php else : ?>
<div class="table-responsive border rounded bg-white shadow-sm">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col"><?= admin_list_sort_th('code', 'Code', $sort, $dir) ?></th>
                <th scope="col">Code filtre FR</th>
                <th scope="col">Code filtre EN</th>
                <th scope="col"><?= admin_list_sort_th('label_fr', 'Libellé FR', $sort, $dir) ?></th>
                <th scope="col">Label EN</th>
                <th scope="col"><?= admin_list_sort_th('contact_email', 'E-mail', $sort, $dir) ?></th>
                <th scope="col" class="text-end"><?= admin_list_sort_th('sort_order', 'Ordre', $sort, $dir) ?></th>
                <th scope="col" class="text-center" style="width:5.5rem"><?= admin_list_sort_th('is_active', 'Actif', $sort, $dir) ?></th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row) :
                $id = (int) ($row['id'] ?? 0);
                $active = (int) ($row['is_active'] ?? 0) === 1;
                ?>
                <tr class="<?= $active ? '' : 'table-secondary' ?>">
                    <td><code class="small"><?= esc((string) ($row['code'] ?? '')) ?></code></td>
                    <td class="small"><code class="text-muted"><?= esc(trim((string) ($row['code_fr'] ?? '')) !== '' ? (string) $row['code_fr'] : '—') ?></code></td>
                    <td class="small"><code class="text-muted"><?= esc(trim((string) ($row['code_en'] ?? '')) !== '' ? (string) $row['code_en'] : '—') ?></code></td>
                    <td class="small"><?= esc((string) ($row['label_fr'] ?? '')) ?></td>
                    <td class="small"><?= esc((string) ($row['label_en'] ?? '')) ?></td>
                    <td class="small"><a href="mailto:<?= esc((string) ($row['contact_email'] ?? ''), 'attr') ?>"><?= esc((string) ($row['contact_email'] ?? '')) ?></a></td>
                    <td class="text-end small"><?= (int) ($row['sort_order'] ?? 0) ?></td>
                    <td class="text-center">
                        <span class="badge rounded-pill px-2 <?= $active ? 'text-bg-success' : 'text-bg-secondary' ?>" title="<?= $active ? 'Visible sur le site' : 'Masqué' ?>">
                            <?= $active ? 'Oui' : 'Non' ?>
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('admin/sectors/edit/' . $id) ?>">Modifier</a>
                        <form action="<?= site_url('admin/sectors/delete/' . $id) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc(lang('Admin.confirm_delete_sector'), 'attr') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => 'secteur(s)']) ?>
<?php endif; ?>
