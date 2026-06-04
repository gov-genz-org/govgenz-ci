<?php

declare(strict_types=1);

helper(['form', 'admin']);

/** @var list<array<string, mixed>> $rows */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $filterPub */
/** @var string $filterLocale */
/** @var string $filterSection */
/** @var string $searchQuery */
/** @var array<string, string> $pubLabels */
/** @var array<string, string> $sectionLabels */
/** @var array<string, string> $kindLabels */
/** @var string $sort */
/** @var string $dir */
/** @var array<string, array<string, true>> $translationLocalesByGroup */

$cmsPageFr = model(\App\Models\CmsPageModel::class)->getPublishedBySlug('declaration', 'fr');
$cmsPageEn = model(\App\Models\CmsPageModel::class)->getPublishedBySlug('declaration', 'en');
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_declarations')) ?></h1>
<p class="text-muted small mb-3"><?= lang('Admin.help_declarations_intro') ?></p>

<div class="alert alert-light border small mb-3" role="note">
    <p class="mb-2 text-muted"><?= lang('Admin.note_declaration_program_split') ?></p>
    <p class="mb-2 d-flex flex-wrap gap-2">
        <?php if (is_array($cmsPageFr) && isset($cmsPageFr['id'])) : ?>
            <a href="<?= site_url('admin/pages/edit/' . (int) $cmsPageFr['id']) ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_edit_declaration_page_fr')) ?></a>
        <?php else : ?>
            <a href="<?= site_url('admin/pages/create') ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_create_declaration_page_fr')) ?></a>
        <?php endif; ?>
        <?php if (is_array($cmsPageEn) && isset($cmsPageEn['id'])) : ?>
            <a href="<?= site_url('admin/pages/edit/' . (int) $cmsPageEn['id']) ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_edit_declaration_page_en')) ?></a>
        <?php endif; ?>
    </p>
    <p class="mb-0 d-flex flex-wrap gap-3">
        <a href="<?= esc(admin_public_declaration_list_url('fr'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_fr')) ?></a>
        <a href="<?= esc(admin_public_declaration_list_url('en'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_en')) ?></a>
    </p>
</div>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/declaration-items/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_declaration_new')) ?></a>
    <form method="get" action="<?= site_url('admin/declaration-items') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="di-q"><?= esc(lang('Admin.filter_search')) ?></label>
            <input type="search" name="q" id="di-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_title_slug'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="di-loc"><?= esc(lang('Admin.filter_locale')) ?></label>
            <select name="loc" id="di-loc" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterLocale === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all')) ?></option>
                <option value="fr" <?= $filterLocale === 'fr' ? 'selected' : '' ?>>FR</option>
                <option value="en" <?= $filterLocale === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="di-section"><?= esc(lang('Admin.decl_filter_section')) ?></label>
            <select name="section" id="di-section" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterSection === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all')) ?></option>
                <?php foreach ($sectionLabels as $code => $lab) : ?>
                    <option value="<?= esc($code) ?>" <?= $filterSection === $code ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="di-pub"><?= esc(lang('Admin.filter_pub_state')) ?></label>
            <select name="pub" id="di-pub" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
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
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_declarations')) ?></p>
        <a href="<?= site_url('admin/declaration-items/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_declaration_new')) ?></a>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <th><?= admin_list_sort_th('locale', lang('Admin.col_locale'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('list_section', lang('Admin.decl_col_section'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('kind', lang('Admin.decl_col_kind'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('slug', lang('Admin.col_slug'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('title', lang('Admin.col_title'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('sort_order', lang('Admin.decl_col_order'), $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('publication_state', lang('Admin.col_publication'), $sort, $dir) ?></th>
        <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row) :
        $id   = (int) ($row['id'] ?? 0);
        $loc  = strtolower((string) ($row['locale'] ?? 'fr'));
        $pub  = (string) ($row['publication_state'] ?? '');
        $slug = (string) ($row['slug'] ?? '');
        $previewUrl = $pub === \App\Models\DeclarationItemModel::PUBLICATION_PUBLISHED && $slug !== ''
            ? admin_public_declaration_item_url($slug, $loc)
            : null;
        ?>
        <tr>
            <td><code class="small"><?= esc(strtoupper($loc)) ?></code></td>
            <td class="small"><?= esc($sectionLabels[(string) ($row['list_section'] ?? '')] ?? '') ?></td>
            <td class="small"><?= esc($kindLabels[(string) ($row['kind'] ?? '')] ?? '') ?></td>
            <td><code class="small"><?= esc($slug) ?></code></td>
            <td><?= esc((string) ($row['title'] ?? '')) ?></td>
            <td class="small text-muted"><?= esc((string) ($row['sort_order'] ?? '0')) ?></td>
            <td>
                <?php if ($pub === 'published') : ?>
                    <span class="badge text-bg-success"><?= esc($pubLabels[$pub] ?? $pub) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-warning text-dark"><?= esc($pubLabels[$pub] ?? $pub) ?></span>
                <?php endif; ?>
            </td>
            <td>
                <?= view('admin/partials/record_list_row_actions', [
                    'previewUrl'           => $previewUrl,
                    'editUrl'              => site_url('admin/declaration-items/edit/' . $id),
                    'duplicateUrl'         => null,
                    'deleteUrl'            => site_url('admin/declaration-items/delete/' . $id),
                    'deleteConfirmMessage' => lang('Admin.confirm_delete_declaration'),
                    'duplicateTradDisabled' => true,
                ], ['saveData' => false]) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_results')]) ?>
<?php endif; ?>
