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
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_positions')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_positions_intro') ?></p>
<?= view('admin/partials/notes/record_list_header', ['listKind' => 'positions']) ?>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/position-items/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_position_new')) ?></a>
    <form method="get" action="<?= site_url('admin/position-items') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="pi-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_title_slug'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-loc"><?= esc(lang('Admin.filter_locale')) ?></label>
            <select name="loc" id="pi-loc" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterLocale === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all')) ?></option>
                <option value="fr" <?= $filterLocale === 'fr' ? 'selected' : '' ?>>FR</option>
                <option value="en" <?= $filterLocale === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="pi-pub"><?= esc(lang('Admin.filter_pub_state')) ?></label>
            <select name="pub" id="pi-pub" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
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
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_positions')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/position-items/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_position_new')) ?></a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <th><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('slug', lang('Admin.col_slug'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('title', lang('Admin.col_title'), $sort, $dir) ?></th>
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
        $tgrp = trim((string) ($row['translation_group'] ?? ''));
        $otherLoc = $loc === 'fr' ? 'en' : 'fr';
        $duplicateTradDisabled = $tgrp !== '' && ! empty($translationLocalesByGroup[$tgrp][$otherLoc]);
        $previewUrl = $pub === \App\Models\PositionItemModel::PUBLICATION_PUBLISHED && $slug !== ''
            ? admin_public_position_url($slug, $loc)
            : null;
        ?>
        <tr>
            <td><code class="small"><?= esc(strtoupper($loc)) ?></code></td>
            <td><code class="small"><?= esc($slug) ?></code></td>
            <td><?= esc((string) ($row['title'] ?? '')) ?></td>
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
                    'editUrl'               => site_url('admin/position-items/edit/' . $id),
                    'duplicateUrl'          => site_url('admin/position-items/duplicate/' . $id),
                    'deleteUrl'             => site_url('admin/position-items/delete/' . $id),
                    'deleteConfirmMessage'  => lang('Admin.confirm_delete_position'),
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
