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
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_projects')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_projects_intro') ?></p>
<?= view('admin/partials/notes/record_list_header', ['listKind' => 'projects']) ?>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/project-projects/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_project_new')) ?></a>
    <form method="get" action="<?= site_url('admin/project-projects') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="pp-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="pp-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_title_slug'), 'attr') ?>" maxlength="120" autocomplete="off">
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
                <option value="" <?= $filterStatus === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all_masc')) ?></option>
                <?php foreach ($statusLabels as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= $filterStatus === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pp-pub"><?= esc(lang('Admin.filter_pub_state')) ?></label>
            <select name="pub" id="pp-pub" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterPub === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all')) ?></option>
                <?php foreach ($pubLabels as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= $filterPub === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_search')) ?></button>
    </form>
</div>

<?php if ($rows === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_projects_filter')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/project-projects/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.action_create_project')) ?></a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <th><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('slug', lang('Admin.col_slug'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('title', lang('Admin.col_title'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('project_status', lang('Admin.col_status'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('publication_state', lang('Admin.col_publication'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('updated_at', lang('Admin.col_updated'), $sort, $dir) ?></th>
        <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row) :
        $id = (int) ($row['id'] ?? 0);
        $slug = (string) ($row['slug'] ?? '');
        $loc = strtolower((string) ($row['locale'] ?? 'fr'));
        if (! in_array($loc, ['fr', 'en'], true)) {
            $loc = 'fr';
        }
        $pub = (string) ($row['publication_state'] ?? '');
        $ps = (string) ($row['project_status'] ?? '');
        $tgrp = trim((string) ($row['translation_group'] ?? ''));
        $otherLoc = $loc === 'fr' ? 'en' : 'fr';
        $duplicateTradDisabled = $tgrp !== '' && ! empty($translationLocalesByGroup[$tgrp][$otherLoc]);
        $previewUrl = $pub === \App\Models\ProjectProjectModel::PUBLICATION_PUBLISHED && $slug !== ''
            ? admin_public_project_url($slug, $loc)
            : null;
        ?>
        <tr>
            <td><code class="small"><?= esc(strtoupper($loc)) ?></code></td>
            <td><code class="small"><?= esc($slug) ?></code></td>
            <td><?= esc((string) ($row['title'] ?? '')) ?></td>
            <td><span class="badge text-bg-secondary"><?= esc($statusLabels[$ps] ?? $ps) ?></span></td>
            <td>
                <?php if ($pub === 'published') : ?>
                    <span class="badge text-bg-success"><?= esc($pubLabels[$pub] ?? $pub) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-warning text-dark"><?= esc($pubLabels[$pub] ?? $pub) ?></span>
                <?php endif; ?>
            </td>
            <td class="small text-nowrap"><?= admin_format_datetime($row['updated_at'] ?? null) ?></td>
            <td>
                <?= view('admin/partials/record_list_row_actions', [
                    'previewUrl'            => $previewUrl,
                    'editUrl'               => site_url('admin/project-projects/edit/' . $id),
                    'duplicateUrl'          => site_url('admin/project-projects/duplicate/' . $id),
                    'deleteUrl'             => site_url('admin/project-projects/delete/' . $id),
                    'deleteConfirmMessage'  => lang('Admin.confirm_delete_project'),
                    'duplicateTradDisabled' => $duplicateTradDisabled,
                ], ['saveData' => false]) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_results')]) ?>
<?php endif; ?>
