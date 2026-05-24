<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $items */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $filterLocale */
/** @var string $filterActive */
/** @var string $searchQuery */
/** @var string $sort */
/** @var string $dir */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_site_menu')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_sitemenu_intro') ?></p>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/site-menu/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_menu_new')) ?></a>
    <form method="get" action="<?= site_url('admin/site-menu') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="menu-search-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="menu-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_menu_search'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="menu-filter-loc"><?= esc(lang('Admin.filter_locale')) ?></label>
            <select name="loc" id="menu-filter-loc" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterLocale === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all')) ?></option>
                <option value="fr" <?= $filterLocale === 'fr' ? 'selected' : '' ?>>FR</option>
                <option value="en" <?= $filterLocale === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="menu-filter-active"><?= esc(lang('Admin.filter_active')) ?></label>
            <select name="active" id="menu-filter-active" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterActive === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all_masc')) ?></option>
                <option value="1" <?= $filterActive === '1' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_yes')) ?></option>
                <option value="0" <?= $filterActive === '0' ? 'selected' : '' ?>><?= esc(lang('Admin.form_sitemenu_no')) ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_search')) ?></button>
    </form>
</div>

<?php if ($items === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_menu')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/site-menu/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_menu_new')) ?></a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <th><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
        <th class="text-end"><?= admin_list_sort_th('sort_order', lang('Admin.col_order'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('label', lang('Admin.col_label'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('href_kind', lang('Admin.col_type'), $sort, $dir) ?></th>
        <th><?= esc(lang('Admin.col_target')) ?></th>
        <th><?= esc(lang('Admin.col_highlight')) ?></th>
        <th><?= admin_list_sort_th('is_active', lang('Admin.col_active'), $sort, $dir) ?></th>
        <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($items as $row) :
        $id = (int) ($row['id'] ?? 0);
        $active = (int) ($row['is_active'] ?? 0) === 1;
        ?>
        <tr>
            <td><code class="small"><?= esc(strtoupper((string) ($row['locale'] ?? 'fr'))) ?></code></td>
            <td class="text-end small"><?= esc((string) ($row['sort_order'] ?? '')) ?></td>
            <td><?= esc((string) ($row['label'] ?? '')) ?></td>
            <td><code class="small"><?= esc((string) ($row['href_kind'] ?? '')) ?></code></td>
            <td class="small text-break"><?= esc((string) ($row['href_target'] ?? '')) ?></td>
            <td><code class="small"><?= esc((string) ($row['match_key'] ?? '')) ?></code></td>
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
                    'editUrl'              => site_url('admin/site-menu/edit/' . $id),
                    'deleteUrl'            => site_url('admin/site-menu/delete/' . $id),
                    'deleteConfirmMessage' => lang('Admin.confirm_delete_menu_entry'),
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
