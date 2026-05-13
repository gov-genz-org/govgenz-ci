<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h3 mb-0">Secteurs</h1>
    <a class="btn btn-primary btn-sm" href="<?= site_url('admin/sectors/create') ?>">Nouveau secteur</a>
</div>
<p class="text-muted small mb-3">
    Libellés <strong>français</strong> et <strong>anglais</strong> (site public + formulaire Rejoindre + projets).
    Dans le CMS, grille dynamique : <code>data-gg-cms="sectors-tile-grid"</code> (EN) ou
    <code>data-gg-cms="secteurs-tile-grid"</code> (FR) — même rendu.
</p>

<div class="table-responsive border rounded bg-white shadow-sm">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">Code</th>
                <th scope="col">Libellé FR</th>
                <th scope="col">Label EN</th>
                <th scope="col">E-mail</th>
                <th scope="col" class="text-end">Ordre</th>
                <th scope="col">Actif</th>
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
                    <td class="small"><?= esc((string) ($row['label_fr'] ?? '')) ?></td>
                    <td class="small"><?= esc((string) ($row['label_en'] ?? '')) ?></td>
                    <td class="small"><a href="mailto:<?= esc((string) ($row['contact_email'] ?? ''), 'attr') ?>"><?= esc((string) ($row['contact_email'] ?? '')) ?></a></td>
                    <td class="text-end small"><?= (int) ($row['sort_order'] ?? 0) ?></td>
                    <td class="small"><?= $active ? 'oui' : 'non' ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('admin/sectors/edit/' . $id) ?>">Modifier</a>
                        <form action="<?= site_url('admin/sectors/delete/' . $id) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="Supprimer définitivement ce secteur ?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if ($rows === []) : ?>
    <p class="text-muted mt-3">Aucun secteur — exécutez les migrations ou créez une entrée.</p>
<?php endif; ?>
