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
<p class="text-muted small mb-3"><?= lang('Admin.help_sectors_index') ?></p>

<?php if ($rows === []) : ?>
    <p class="text-muted"><?= esc(lang('Admin.empty_no_sectors')) ?></p>
<?php else : ?>
<div class="table-responsive border rounded bg-white shadow-sm">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col"><?= admin_list_sort_th('code', lang('Admin.col_code'), $sort, $dir) ?></th>
                <th scope="col"><?= esc(lang('Admin.col_filter_code_fr')) ?></th>
                <th scope="col"><?= esc(lang('Admin.col_filter_code_en')) ?></th>
                <th scope="col"><?= admin_list_sort_th('label_fr', lang('Admin.col_label_fr'), $sort, $dir) ?></th>
                <th scope="col"><?= esc(lang('Admin.col_label_en')) ?></th>
                <th scope="col"><?= admin_list_sort_th('contact_email', lang('Admin.col_email'), $sort, $dir) ?></th>
                <th scope="col" class="text-end"><?= admin_list_sort_th('sort_order', lang('Admin.col_order'), $sort, $dir) ?></th>
                <th scope="col" class="text-center" style="width:5.5rem"><?= admin_list_sort_th('is_active', lang('Admin.col_active'), $sort, $dir) ?></th>
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
                        <span class="badge rounded-pill px-2 <?= $active ? 'text-bg-success' : 'text-bg-secondary' ?>" title="<?= esc($active ? lang('Admin.tooltip_sector_visible') : lang('Admin.tooltip_sector_hidden'), 'attr') ?>">
                            <?= esc($active ? lang('Admin.ui_yes') : lang('Admin.ui_no')) ?>
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('admin/sectors/edit/' . $id) ?>"><?= esc(lang('Admin.action_modify')) ?></a>
                        <form action="<?= site_url('admin/sectors/delete/' . $id) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc(lang('Admin.confirm_delete_sector'), 'attr') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Admin.action_delete')) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_sectors')]) ?>
<?php endif; ?>
