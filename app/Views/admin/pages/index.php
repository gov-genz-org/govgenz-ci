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
<p class="text-muted small mb-3">Pages fixes du site (accueil, à propos, contact…). Seules les URL connues du routage ont un lien « Site ».</p>
<div class="alert alert-secondary py-2 px-3 small mb-3" role="note">
    <strong>Pied de page (colonnes)</strong> : pour les modifier depuis l’admin, créez une page <strong>publiée</strong> avec le slug exact <code>site-footer</code> (une variante FR et une EN). Son corps remplace les trois colonnes du footer&nbsp;; la route publique <code>/site-footer</code> est désactivée. La marque (logo, devise, ligne légale) reste dans le gabarit tant que vous ne demandez pas de les sortir aussi en CMS.
</div>
<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/pages/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.breadcrumb_page_new')) ?></a>
    <form method="get" action="<?= site_url('admin/pages') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="pages-search-q">Recherche</label>
            <input type="search" name="q" id="pages-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="Titre ou slug…" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="filter-status">Statut</label>
            <select name="status" id="filter-status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Tous</option>
                <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>>Publié</option>
                <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>>Brouillon</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Rechercher</button>
    </form>
</div>

<?php if ($pages === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted">Aucune page pour ce filtre.</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/pages/create') ?>" class="btn btn-primary btn-sm">Créer une page</a>
            <a href="<?= site_url('admin/media') ?>" class="btn btn-outline-secondary btn-sm">Ouvrir la médiathèque</a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr>
        <th><?= admin_list_sort_th('locale', 'Langue', $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('slug', 'Slug', $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('title', 'Titre', $sort, $dir) ?></th>
        <th><?= admin_list_sort_th('status', 'Statut', $sort, $dir) ?></th>
        <th>Site</th>
        <th class="text-end">Actions</th>
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
                    <a href="<?= esc($pubUrl) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">Voir</a>
                <?php else : ?>
                    <span class="text-muted small">—</span>
                <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
                <a href="<?= site_url('admin/pages/edit/' . $p['id']) ?>" class="btn btn-outline-secondary btn-sm">Éditer</a>
                <form action="<?= site_url('admin/pages/duplicate/' . $p['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-primary btn-sm" <?= $duplicateTradDisabled ? 'disabled title="' . esc(lang('Admin.tooltip_duplicate_trad_disabled'), 'attr') . '"' : '' ?>><?= esc(lang('Admin.action_duplicate_trad')) ?></button>
                </form>
                <form action="<?= site_url('admin/pages/delete/' . $p['id']) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc(lang('Admin.confirm_delete_page'), 'attr') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => 'résultat(s)']) ?>
<?php endif; ?>

