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
<?= view('admin/partials/notes/positions_program') ?>

<div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <a href="<?= site_url('admin/position-items/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_position_new')) ?></a>
    <form method="get" action="<?= site_url('admin/position-items') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="pi-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" maxlength="120">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-loc"><?= esc(lang('Admin.filter_locale')) ?></label>
            <select name="loc" id="pi-loc" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="" <?= $filterLocale === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all')) ?></option>
                <option value="fr" <?= $filterLocale === 'fr' ? 'selected' : '' ?>>FR</option>
                <option value="en" <?= $filterLocale === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-pub"><?= esc(lang('Admin.filter_pub_state')) ?></label>
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
            <th><?= admin_list_sort_th('slug', lang('Admin.col_slug'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('title', lang('Admin.col_title'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('publication_state', lang('Admin.col_pub_short'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('updated_at', lang('Admin.col_updated'), $sort, $dir) ?></th>
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
                        <a href="<?= site_url('admin/position-items/edit/' . $id) ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_modify')) ?></a>
                        <form method="post" action="<?= site_url('admin/position-items/duplicate/' . $id) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary" <?= $duplicateTradDisabled ? 'disabled title="' . esc(lang('Admin.tooltip_duplicate_trad_disabled'), 'attr') . '"' : '' ?>><?= esc(lang('Admin.action_duplicate_trad')) ?></button>
                        </form>
                        <form method="post" action="<?= site_url('admin/position-items/delete/' . $id) ?>" class="d-inline" onsubmit="return confirm('Supprimer cette position ?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger"><?= esc(lang('Admin.action_delete')) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $pager->links() ?>
