<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $rows */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $filterActive */
/** @var string $searchQuery */
/** @var string $sort */
/** @var string $dir */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_sectors')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_sectors_intro') ?></p>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/sectors/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_sector_new')) ?></a>
    <form method="get" action="<?= site_url('admin/sectors') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="sectors-search-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="sectors-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_sector_search'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="sectors-filter-active"><?= esc(lang('Admin.filter_active')) ?></label>
            <select name="active" id="sectors-filter-active" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterActive === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all_masc')) ?></option>
                <option value="1" <?= $filterActive === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sector_active_yes')) ?></option>
                <option value="0" <?= $filterActive === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sector_active_no')) ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_search')) ?></button>
    </form>
</div>

<?php if ($rows === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_sectors')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/sectors/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_sector_new')) ?></a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <th><?= admin_list_sort_th('code', lang('Admin.col_code'), $sort, $dir) ?></th>
        <th><?= esc(lang('Admin.col_filter_code_fr')) ?></th>
        <th><?= esc(lang('Admin.col_filter_code_en')) ?></th>
        <th><?= admin_list_sort_th('label_fr', lang('Admin.col_label_fr'), $sort, $dir) ?></th>
        <th><?= esc(lang('Admin.col_label_en')) ?></th>
        <th><?= admin_list_sort_th('contact_email', lang('Admin.col_email'), $sort, $dir) ?></th>
        <th class="text-end"><?= admin_list_sort_th('sort_order', lang('Admin.col_order'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('is_active', lang('Admin.col_active'), $sort, $dir) ?></th>
        <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row) :
        $id = (int) ($row['id'] ?? 0);
        $active = (int) ($row['is_active'] ?? 0) === 1;
        ?>
        <tr>
            <td><code class="small"><?= esc((string) ($row['code'] ?? '')) ?></code></td>
            <td><code class="small text-muted"><?= esc(trim((string) ($row['code_fr'] ?? '')) !== '' ? (string) $row['code_fr'] : '—') ?></code></td>
            <td><code class="small text-muted"><?= esc(trim((string) ($row['code_en'] ?? '')) !== '' ? (string) $row['code_en'] : '—') ?></code></td>
            <td><?= esc((string) ($row['label_fr'] ?? '')) ?></td>
            <td><?= esc((string) ($row['label_en'] ?? '')) ?></td>
            <td class="small"><a href="mailto:<?= esc((string) ($row['contact_email'] ?? ''), 'attr') ?>"><?= esc((string) ($row['contact_email'] ?? '')) ?></a></td>
            <td class="text-end small"><?= (int) ($row['sort_order'] ?? 0) ?></td>
            <td>
                <?php if ($active) : ?>
                    <span class="badge text-bg-success"><?= esc(lang('Admin.ui_yes')) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-secondary"><?= esc(lang('Admin.ui_no')) ?></span>
                <?php endif; ?>
            </td>
            <td>
                <?= view('admin/partials/record_list_row_actions', [
                    'previewUrl'           => null,
                    'editUrl'              => site_url('admin/sectors/edit/' . $id),
                    'deleteUrl'            => site_url('admin/sectors/delete/' . $id),
                    'deleteConfirmMessage' => lang('Admin.confirm_delete_sector'),
                    'showDuplicateTrad'    => false,
                ], ['saveData' => false]) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_results')]) ?>
<?php endif; ?>
