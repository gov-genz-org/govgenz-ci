<?php

declare(strict_types=1);

helper(['form', 'admin', 'position']);

/** @var list<array<string, mixed>> $rows */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $filterPub */
/** @var string $filterLocale */
/** @var string $searchQuery */
/** @var array<string, string> $pubLabels */
/** @var string $sort */
/** @var string $dir */
/** @var array<string, array<string, true>> $translationLocalesByGroup */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_positions_program')) ?></h1>
<div class="alert alert-light border small mb-3" role="note">
    <p class="mb-2">Fiches dynamiques sur <code>/positions</code>. Bandeau liste : pages CMS <code>positions-programme</code> (FR) et <code>positions-program</code> (EN).</p>
    <p class="mb-0 d-flex flex-wrap gap-3">
        <a href="<?= site_url('admin/pages') ?>" class="btn btn-sm btn-outline-secondary">Pages CMS</a>
        <a href="<?= esc(admin_public_positions_program_list_url('fr'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Liste FR</a>
        <a href="<?= esc(admin_public_positions_program_list_url('en'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Liste EN</a>
    </p>
</div>

<div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <a href="<?= site_url('admin/position-items/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_position_new')) ?></a>
    <form method="get" action="<?= site_url('admin/position-items') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-q">Recherche</label>
            <input type="search" name="q" id="pi-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" maxlength="120">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-loc">Langue</label>
            <select name="loc" id="pi-loc" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="" <?= $filterLocale === 'all' ? 'selected' : '' ?>>Toutes</option>
                <option value="fr" <?= $filterLocale === 'fr' ? 'selected' : '' ?>>FR</option>
                <option value="en" <?= $filterLocale === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-pub">Publication</label>
            <select name="pub" id="pi-pub" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="" <?= $filterPub === 'all' ? 'selected' : '' ?>>Toutes</option>
                <?php foreach ($pubLabels as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= $filterPub === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrer</button>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
        <thead>
        <tr>
            <th><?= admin_list_sort_th('slug', 'Slug', $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('locale', 'Langue', $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('title', 'Titre', $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('publication_state', 'Pub.', $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('updated_at', 'Maj', $sort, $dir) ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []) : ?>
            <tr><td colspan="6" class="text-muted">Aucune position.</td></tr>
        <?php else : ?>
            <?php foreach ($rows as $row) :
                $id = (int) ($row['id'] ?? 0);
                $slug = (string) ($row['slug'] ?? '');
                $loc = strtolower((string) ($row['locale'] ?? 'fr'));
                if (! in_array($loc, ['fr', 'en'], true)) {
                    $loc = 'fr';
                }
                $pub = (string) ($row['publication_state'] ?? '');
                $tgrp = trim((string) ($row['translation_group'] ?? ''));
                $otherLoc = $loc === 'fr' ? 'en' : 'fr';
                $duplicateTradDisabled = $tgrp !== '' && ! empty($translationLocalesByGroup[$tgrp][$otherLoc]);
                $preview = $pub === \App\Models\PositionItemModel::PUBLICATION_PUBLISHED && $slug !== ''
                    ? admin_public_position_url($slug, $loc)
                    : null;
                ?>
                <tr>
                    <td class="font-monospace small"><?= esc($slug) ?></td>
                    <td><?= esc(strtoupper($loc)) ?></td>
                    <td><?= esc((string) ($row['title'] ?? '')) ?></td>
                    <td><span class="badge bg-secondary"><?= esc($pubLabels[$pub] ?? $pub) ?></span></td>
                    <td class="small text-muted"><?= esc((string) ($row['updated_at'] ?? '')) ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($preview !== null) : ?>
                            <a href="<?= esc($preview, 'attr') ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Voir</a>
                        <?php endif; ?>
                        <a href="<?= site_url('admin/position-items/edit/' . $id) ?>" class="btn btn-sm btn-outline-secondary">Modifier</a>
                        <form method="post" action="<?= site_url('admin/position-items/duplicate/' . $id) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary" <?= $duplicateTradDisabled ? 'disabled title="' . esc(lang('Admin.tooltip_duplicate_trad_disabled'), 'attr') . '"' : '' ?>><?= esc(lang('Admin.action_duplicate_trad')) ?></button>
                        </form>
                        <form method="post" action="<?= site_url('admin/position-items/delete/' . $id) ?>" class="d-inline" onsubmit="return confirm('Supprimer cette position ?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $pager->links() ?>
