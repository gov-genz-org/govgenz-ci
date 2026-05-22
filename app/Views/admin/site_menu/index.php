<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $items */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.nav_site_menu')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_sitemenu_index')) ?></p>

<div class="mb-3">
    <a href="<?= site_url('admin/site-menu/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_menu_new')) ?></a>
</div>

<?php if ($items === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_menu')) ?></p>
        <a href="<?= site_url('admin/site-menu/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.action_create_menu_entry')) ?></a>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light">
    <tr>
        <th scope="col"><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('sort_order', lang('Admin.col_order'), $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('label', lang('Admin.col_label'), $sort, $dir) ?></th>
        <th scope="col"><?= admin_list_sort_th('href_kind', lang('Admin.col_type'), $sort, $dir) ?></th>
        <th scope="col"><?= esc(lang('Admin.col_target')) ?></th>
        <th scope="col"><?= esc(lang('Admin.col_highlight')) ?></th>
        <th scope="col"><?= admin_list_sort_th('is_active', lang('Admin.col_active'), $sort, $dir) ?></th>
        <th scope="col" class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $row) :
        $id = (int) ($row['id'] ?? 0);
        ?>
        <tr>
            <td><span class="badge text-bg-light border"><?= esc(strtoupper((string) ($row['locale'] ?? 'fr'))) ?></span></td>
            <td class="small"><?= esc((string) ($row['sort_order'] ?? '')) ?></td>
            <td><?= esc((string) ($row['label'] ?? '')) ?></td>
            <td><code class="small"><?= esc((string) ($row['href_kind'] ?? '')) ?></code></td>
            <td class="small text-break"><?= esc((string) ($row['href_target'] ?? '')) ?></td>
            <td><code class="small"><?= esc((string) ($row['match_key'] ?? '')) ?></code></td>
            <td>
                <?php if ((int) ($row['is_active'] ?? 0) === 1) : ?>
                    <span class="badge text-bg-success"><?= esc(lang('Admin.ui_yes')) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-secondary"><?= esc(lang('Admin.ui_no')) ?></span>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <a href="<?= site_url('admin/site-menu/edit/' . $id) ?>" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_edit')) ?></a>
                <form action="<?= site_url('admin/site-menu/delete/' . $id) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc(lang('Admin.confirm_delete_menu_entry'), 'attr') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger"><?= esc(lang('Admin.action_delete')) ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_entries')]) ?>
<?php endif; ?>
