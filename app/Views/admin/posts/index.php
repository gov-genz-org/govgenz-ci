<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $posts */
/** @var string $filterStatus */
/** @var string $searchQuery */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */
/** @var array<string, array<string, true>> $translationLocalesByGroup */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_posts')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_posts_intro') ?></p>
<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/posts/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_post_new')) ?></a>
    <form method="get" action="<?= site_url('admin/posts') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="posts-search-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="posts-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_title_slug'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="filter-post-status"><?= esc(lang('Admin.filter_status')) ?></label>
            <select name="status" id="filter-post-status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterStatus === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all_masc')) ?></option>
                <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_published')) ?></option>
                <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_draft')) ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_search')) ?></button>
    </form>
</div>

<?php if ($posts === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_posts')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/posts/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.action_write_post')) ?></a>
            <a href="<?= site_url('admin/media') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.empty_media_for_posts')) ?></a>
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
        <th><?= admin_list_sort_th('published_at', lang('Admin.col_published_at'), $sort, $dir) ?></th>
        <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($posts as $post) :
        $tgrp = trim((string) ($post['translation_group'] ?? ''));
        $loc = strtolower((string) ($post['locale'] ?? 'fr'));
        if (! in_array($loc, ['fr', 'en'], true)) {
            $loc = 'fr';
        }
        $otherLoc              = $loc === 'fr' ? 'en' : 'fr';
        $duplicateTradDisabled = $tgrp !== '' && ! empty($translationLocalesByGroup[$tgrp][$otherLoc]);
        $previewUrl = (($post['status'] ?? '') === 'published' && ($post['slug'] ?? '') !== '')
            ? admin_public_press_url((string) $post['slug'], $loc)
            : null;
        ?>
        <tr>
            <td><code class="small"><?= esc(strtoupper((string) ($post['locale'] ?? 'fr'))) ?></code></td>
            <td><code class="small"><?= esc($post['slug']) ?></code></td>
            <td><?= esc($post['title']) ?></td>
            <td>
                <?php if (($post['status'] ?? '') === 'published') : ?>
                    <span class="badge text-bg-success"><?= esc(lang('Admin.filter_published')) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-warning text-dark"><?= esc(lang('Admin.filter_draft')) ?></span>
                <?php endif; ?>
            </td>
            <td class="small text-nowrap"><?= admin_format_datetime($post['published_at'] ?? null) ?></td>
            <td>
                <?= view('admin/partials/record_list_row_actions', [
                    'previewUrl'            => $previewUrl,
                    'editUrl'               => site_url('admin/posts/edit/' . $post['id']),
                    'duplicateUrl'          => site_url('admin/posts/duplicate/' . $post['id']),
                    'deleteUrl'             => site_url('admin/posts/delete/' . $post['id']),
                    'deleteConfirmMessage'  => lang('Admin.confirm_delete_post'),
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
