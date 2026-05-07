<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $posts */
/** @var string $filterStatus */
/** @var string $searchQuery */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var array<string, array<string, true>> $translationLocalesByGroup */
?>
<h1 class="h3 mb-1">Articles</h1>
<p class="text-muted small mb-3">Communiqués et articles affichés sous <strong>/press</strong>.</p>
<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= site_url('admin/posts/create') ?>" class="btn btn-primary btn-sm">Nouvel article</a>
    <form method="get" action="<?= site_url('admin/posts') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <div>
            <label class="small text-muted mb-0 d-block" for="posts-search-q">Recherche</label>
            <input type="search" name="q" id="posts-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="Titre ou slug…" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="filter-post-status">Statut</label>
            <select name="status" id="filter-post-status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Tous</option>
                <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>>Publié</option>
                <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>>Brouillon</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Rechercher</button>
    </form>
</div>

<?php if ($posts === []) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted">Aucun article pour ce filtre.</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/posts/create') ?>" class="btn btn-primary btn-sm">Rédiger un article</a>
            <a href="<?= site_url('admin/media') ?>" class="btn btn-outline-secondary btn-sm">Médiathèque (illustrations)</a>
        </div>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr><th>Langue</th><th>Slug</th><th>Titre</th><th>Statut</th><th>Publié le</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($posts as $post) :
        $tgrp = trim((string) ($post['translation_group'] ?? ''));
        $loc = strtolower((string) ($post['locale'] ?? 'fr'));
        if (! in_array($loc, ['fr', 'en'], true)) {
            $loc = 'fr';
        }
        $otherLoc              = $loc === 'fr' ? 'en' : 'fr';
        $duplicateTradDisabled = $tgrp !== '' && ! empty($translationLocalesByGroup[$tgrp][$otherLoc]);
        ?>
        <tr>
            <td><code class="small"><?= esc(strtoupper((string) ($post['locale'] ?? 'fr'))) ?></code></td>
            <td><code class="small"><?= esc($post['slug']) ?></code></td>
            <td><?= esc($post['title']) ?></td>
            <td>
                <?php if (($post['status'] ?? '') === 'published') : ?>
                    <span class="badge text-bg-success">Publié</span>
                <?php else : ?>
                    <span class="badge text-bg-warning text-dark">Brouillon</span>
                <?php endif; ?>
            </td>
            <td class="small"><?= esc($post['published_at'] ?? '') ?></td>
            <td class="text-end text-nowrap">
                <?php if (($post['status'] ?? '') === 'published') : ?>
                    <a href="<?= site_url('press/' . $post['slug']) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">Voir</a>
                <?php endif; ?>
                <a href="<?= site_url('admin/posts/edit/' . $post['id']) ?>" class="btn btn-outline-secondary btn-sm">Éditer</a>
                <form action="<?= site_url('admin/posts/duplicate/' . $post['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-primary btn-sm" <?= $duplicateTradDisabled ? 'disabled title="Une traduction existe déjà pour l’autre langue."' : '' ?>>Dupliquer trad</button>
                </form>
                <form action="<?= site_url('admin/posts/delete/' . $post['id']) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="Supprimer définitivement cet article ?">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
    <div><?= (int) $pager->getTotal('default') ?> résultat(s)</div>
    <?php if ($pager->getPageCount('default') > 1) : ?>
        <?= $pager->links('default', 'bootstrap_full') ?>
    <?php endif; ?>
</div>
<?php endif; ?>
