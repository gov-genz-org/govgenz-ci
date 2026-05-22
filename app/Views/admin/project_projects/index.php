<?php

declare(strict_types=1);

helper(['form', 'admin']);

/** @var list<array<string, mixed>> $rows */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $filterStatus */
/** @var string $filterPub */
/** @var string $filterLocale */
/** @var string $searchQuery */
/** @var array<string, string> $statusLabels */
/** @var array<string, string> $pubLabels */
/** @var string $sort */
/** @var string $dir */
/** @var array<string, array<string, true>> $translationLocalesByGroup */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_projects_program')) ?></h1>
<?= view('admin/partials/notes/projects_program') ?>

<div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <a href="<?= site_url('admin/project-projects/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_project_new')) ?></a>
    <form method="get" action="<?= site_url('admin/project-projects') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="pp-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="pp-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_title_slug'), 'attr') ?>" maxlength="120">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pp-loc"><?= esc(lang('Admin.filter_locale')) ?></label>
            <select name="loc" id="pp-loc" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterLocale === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all')) ?></option>
                <option value="fr" <?= $filterLocale === 'fr' ? 'selected' : '' ?>>FR</option>
                <option value="en" <?= $filterLocale === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pp-st"><?= esc(lang('Admin.filter_business_status')) ?></label>
            <select name="status" id="pp-st" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Tous</option>
                <?php foreach ($statusLabels as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= $filterStatus === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pp-pub">Publication</label>
            <select name="pub" id="pp-pub" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterPub === 'all' ? 'selected' : '' ?>>Toutes</option>
                <?php foreach ($pubLabels as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= $filterPub === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filtrer</button>
    </form>
</div>

<?php if ($rows === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted">Aucun projet pour ce filtre.</p>
        <a href="<?= site_url('admin/project-projects/create') ?>" class="btn btn-outline-primary btn-sm">Créer un projet</a>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-hover align-middle mb-0 small">
    <thead class="table-light">
        <tr>
            <th><?= admin_list_sort_th('slug', lang('Admin.col_slug'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('title', lang('Admin.col_title'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('project_status', lang('Admin.col_status'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('publication_state', lang('Admin.col_publication'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('updated_at', lang('Admin.col_updated'), $sort, $dir) ?></th>
            <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row) :
            $id = (int) ($row['id'] ?? 0);
            $ps = (string) ($row['project_status'] ?? '');
            $pub = (string) ($row['publication_state'] ?? '');
            $loc = strtolower((string) ($row['locale'] ?? 'fr'));
            if (! in_array($loc, ['fr', 'en'], true)) {
                $loc = 'fr';
            }
            $tgrp = trim((string) ($row['translation_group'] ?? ''));
            $otherLoc              = $loc === 'fr' ? 'en' : 'fr';
            $duplicateTradDisabled = $tgrp !== '' && ! empty($translationLocalesByGroup[$tgrp][$otherLoc]);
            ?>
        <tr>
            <td><code><?= esc((string) ($row['slug'] ?? '')) ?></code></td>
            <td><span class="badge text-bg-light border"><?= esc(strtoupper($loc)) ?></span></td>
            <td><?= esc((string) ($row['title'] ?? '')) ?></td>
            <td><span class="badge text-bg-secondary"><?= esc($statusLabels[$ps] ?? $ps) ?></span></td>
            <td><span class="badge <?= $pub === 'published' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= esc($pubLabels[$pub] ?? $pub) ?></span></td>
            <td class="text-nowrap"><?= admin_format_datetime($row['updated_at'] ?? null) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= site_url('admin/project-projects/edit/' . $id) ?>" class="btn btn-outline-primary btn-sm"><?= esc(lang('Admin.action_modify')) ?></a>
                        <form action="<?= site_url('admin/project-projects/duplicate/' . $id) ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-primary btn-sm" <?= $duplicateTradDisabled ? 'disabled title="' . esc(lang('Admin.tooltip_duplicate_trad_disabled'), 'attr') . '"' : '' ?>><?= esc(lang('Admin.action_duplicate_trad')) ?></button>
                        </form>
                        <form action="<?= site_url('admin/project-projects/delete/' . $id) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc(lang('Admin.confirm_delete_project'), 'attr') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Admin.action_delete')) ?></button>
                        </form>
                    </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => 'résultat(s)']) ?>
<?php endif; ?>
