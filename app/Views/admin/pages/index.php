<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $pages */
/** @var string $filterStatus */
/** @var string $searchQuery */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */
/** @var array<string, array<string, true>> $translationLocalesByGroup */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_pages')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_pages_intro')) ?></p>
<?= view('admin/partials/notes/pages_footer') ?>
<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/pages/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_page_new')) ?></a>
    <form method="get" action="<?= site_url('admin/pages') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="pages-search-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="pages-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_title_slug'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="filter-status"><?= esc(lang('Admin.filter_status')) ?></label>
            <select name="status" id="filter-status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterStatus === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all_masc')) ?></option>
                <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_published')) ?></option>
                <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_draft')) ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_search')) ?></button>
    </form>
</div>

<?php if ($pages === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_pages')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/pages/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.empty_create_page')) ?></a>
            <a href="<?= site_url('admin/media') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.empty_open_media')) ?></a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <th><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('slug', lang('Admin.col_slug'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('title', lang('Admin.col_title'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('status', lang('Admin.col_status'), $sort, $dir) ?></th>
        <th><?= esc(lang('Admin.col_site')) ?></th>
        <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($pages as $p) :
        $pubUrl = (($p['status'] ?? '') === 'published') ? admin_public_page_url((string) ($p['slug'] ?? ''), (string) ($p['locale'] ?? 'fr')) : null;
        $tgrp = trim((string) ($p['translation_group'] ?? ''));
        $loc = strtolower((string) ($p['locale'] ?? 'fr'));
        if (! in_array($loc, ['fr', 'en'], true)) {
            $loc = 'fr';
        }
        $otherLoc              = $loc === 'fr' ? 'en' : 'fr';
        $duplicateTradDisabled = $tgrp !== '' && ! empty($translationLocalesByGroup[$tgrp][$otherLoc]);
        ?>
        <tr>
            <td><code class="small"><?= esc(strtoupper((string) ($p['locale'] ?? 'fr'))) ?></code></td>
            <td><code class="small"><?= esc($p['slug']) ?></code></td>
            <td><?= esc($p['title']) ?></td>
            <td>
                <?php if (($p['status'] ?? '') === 'published') : ?>
                    <span class="badge text-bg-success">Publié</span>
                <?php else : ?>
                    <span class="badge text-bg-warning text-dark">Brouillon</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($pubUrl !== null) : ?>
                    <a href="<?= esc($pubUrl) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><?= esc(lang('Admin.action_view')) ?></a>
                <?php else : ?>
                    <span class="text-muted small">—</span>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <a href="<?= site_url('admin/pages/edit/' . $p['id']) ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_edit')) ?></a>
                <form action="<?= site_url('admin/pages/duplicate/' . $p['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-primary btn-sm" <?= $duplicateTradDisabled ? 'disabled title="' . esc(lang('Admin.tooltip_duplicate_trad_disabled'), 'attr') . '"' : '' ?>><?= esc(lang('Admin.action_duplicate_trad')) ?></button>
                </form>
                <form action="<?= site_url('admin/pages/delete/' . $p['id']) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc(lang('Admin.confirm_delete_page'), 'attr') ?>">
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

