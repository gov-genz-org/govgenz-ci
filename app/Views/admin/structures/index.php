<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $rows */
/** @var \CodeIgniter\Pager\Pager|null $pager */
/** @var string $filterActive */
/** @var string $searchQuery */
/** @var string $sort */
/** @var string $dir */
/** @var bool $enableSortableList */
/** @var string $reorderUrl */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_structures')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_structures_intro') ?></p>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/structures/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_structure_new')) ?></a>
    <form method="get" action="<?= site_url('admin/structures') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="structures-search-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="structures-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_structure_search'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="structures-filter-active"><?= esc(lang('Admin.filter_active')) ?></label>
            <select name="active" id="structures-filter-active" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterActive === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all_masc')) ?></option>
                <option value="1" <?= $filterActive === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_structure_active_yes')) ?></option>
                <option value="0" <?= $filterActive === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_structure_active_no')) ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_search')) ?></button>
    </form>
</div>

<?php if ($rows === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_structures')) ?></p>
        <a href="<?= site_url('admin/structures/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_structure_new')) ?></a>
    </div>
<?php else : ?>
<?php if (! empty($enableSortableList)) : ?>
    <p class="alert alert-light border small py-2 mb-3"><?= lang('Admin.help_list_drag_reorder') ?></p>
<?php endif; ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <?php if (! empty($enableSortableList)) : ?>
            <th class="text-center" style="width:2.75rem" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>"></th>
        <?php endif; ?>
        <th><?= admin_list_sort_th('code', lang('Admin.col_code'), $sort, $dir) ?></th>
        <th><?= esc(lang('Admin.col_structure_role')) ?></th>
        <th><?= admin_list_sort_th('title_fr', lang('Admin.col_label_fr'), $sort, $dir) ?></th>
        <th><?= esc(lang('Admin.col_label_en')) ?></th>
        <th><?= admin_list_sort_th('contact_email', lang('Admin.col_email'), $sort, $dir) ?></th>
        <th class="text-end"><?= admin_list_sort_th('sort_order', lang('Admin.col_order'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('is_active', lang('Admin.col_active'), $sort, $dir) ?></th>
        <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr></thead>
    <tbody
        <?= ! empty($enableSortableList) ? ' data-admin-sortable-list data-reorder-url="' . esc($reorderUrl, 'attr') . '" data-csrf-name="' . esc(csrf_token(), 'attr') . '" data-csrf-hash="' . esc(csrf_hash(), 'attr') . '"' : '' ?>
    >
    <?php foreach ($rows as $row) :
        $id = (int) ($row['id'] ?? 0);
        $active = (int) ($row['is_active'] ?? 0) === 1;
        ?>
        <tr<?= ! empty($enableSortableList) ? ' data-sortable-id="' . esc((string) $id, 'attr') . '"' : '' ?>>
            <?php if (! empty($enableSortableList)) : ?>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary admin-sortable-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
                </td>
            <?php endif; ?>
            <td><code class="small"><?= esc((string) ($row['code'] ?? '')) ?></code></td>
            <td class="small">
                <?php if (strtolower(trim((string) ($row['unit_role'] ?? ''))) === 'core') : ?>
                    <?= esc(lang('Admin.structure_role_core')) ?>
                <?php else : ?>
                    <?= esc(lang('Admin.structure_role_function')) ?>
                <?php endif; ?>
            </td>
            <td><?= esc((string) ($row['title_fr'] ?? '')) ?></td>
            <td><?= esc((string) ($row['title_en'] ?? '')) ?></td>
            <td class="small"><a href="mailto:<?= esc((string) ($row['contact_email'] ?? ''), 'attr') ?>"><?= esc((string) ($row['contact_email'] ?? '')) ?></a></td>
            <td class="text-end small"<?= ! empty($enableSortableList) ? ' data-sort-order-cell' : '' ?>><?= (int) ($row['sort_order'] ?? 0) ?></td>
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
                    'editUrl'              => site_url('admin/structures/edit/' . $id),
                    'deleteUrl'            => site_url('admin/structures/delete/' . $id),
                    'deleteConfirmMessage' => lang('Admin.confirm_delete_structure'),
                ]) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if ($pager !== null) : ?>
    <?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_results')]) ?>
<?php endif; ?>
<?php endif; ?>
