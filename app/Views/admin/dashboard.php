<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $recentPages */
/** @var list<array<string, mixed>> $recentPosts */

?>

<h1 class="h3 mb-3"><?= esc(lang('Admin.title_dashboard')) ?></h1>
<p class="text-muted mb-4">Connecté en tant que <?= esc(session()->get('staff_email') ?? '') ?></p>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="small text-muted text-uppercase">Pages</div>
                <div class="fs-4 fw-semibold"><?= esc((string) $pagesTotal) ?></div>
                <div class="small mt-1"><span class="text-success"><?= esc((string) $pagesPublished) ?></span> publiées · <span class="text-warning"><?= esc((string) $pagesDraft) ?></span> brouillons</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="small text-muted text-uppercase">Presse</div>
                <div class="fs-4 fw-semibold"><?= esc((string) $postsTotal) ?></div>
                <div class="small mt-1"><span class="text-success"><?= esc((string) $postsPublished) ?></span> publiés · <span class="text-warning"><?= esc((string) $postsDraft) ?></span> brouillons</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="small text-muted text-uppercase">Médias</div>
                <div class="fs-4 fw-semibold"><?= esc((string) $mediaTotal) ?></div>
                <div class="small mt-1"><a href="<?= site_url('admin/media') ?>" class="text-decoration-none">Ouvrir la médiathèque →</a></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="small text-muted text-uppercase">Volontaires</div>
                <div class="fs-4 fw-semibold"><?= esc((string) $volunteersTotal) ?></div>
                <div class="small mt-1"><?php if ($volunteersNew > 0) : ?><span class="badge text-bg-primary"><?= esc((string) $volunteersNew) ?> nouveau(x)</span><?php else : ?>Aucune nouvelle candidature<?php endif; ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="small text-muted text-uppercase">Financements</div>
                <div class="fs-4 fw-semibold"><?= esc((string) ($contributionsTotal ?? 0)) ?></div>
                <div class="small mt-1">
                    <?php if (($contributionsNew ?? 0) > 0) : ?>
                        <a href="<?= site_url('admin/project-contributions?status=new') ?>" class="badge text-bg-primary text-decoration-none"><?= esc((string) $contributionsNew) ?> à valider</a>
                    <?php else : ?>
                        <a href="<?= site_url('admin/project-contributions') ?>" class="text-decoration-none">Voir les propositions →</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<h2 class="h6 text-muted text-uppercase mb-3 mt-4">Activité récente</h2>
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
                <span class="fw-semibold small">Dernières pages</span>
                <a href="<?= site_url('admin/pages') ?>" class="small text-decoration-none">Tout voir →</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php if ($recentPages === []) : ?>
                    <li class="list-group-item text-muted small py-3">Aucune page.</li>
                <?php else : ?>
                    <?php foreach ($recentPages as $rp) :
                        $rid = (int) ($rp['id'] ?? 0);
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-2 py-2">
                            <div class="min-w-0 flex-grow-1">
                                <a href="<?= site_url('admin/pages/edit/' . $rid) ?>" class="fw-medium text-decoration-none text-truncate d-block"><?= esc((string) ($rp['title'] ?? '')) ?></a>
                                <span class="small text-muted"><code class="small"><?= esc((string) ($rp['slug'] ?? '')) ?></code> · <?= admin_format_datetime($rp['updated_at'] ?? null) ?></span>
                            </div>
                            <?php if (($rp['status'] ?? '') === 'published') : ?>
                                <span class="badge text-bg-success align-self-center flex-shrink-0">Publié</span>
                            <?php else : ?>
                                <span class="badge text-bg-warning text-dark align-self-center flex-shrink-0">Brouillon</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
                <span class="fw-semibold small">Derniers articles</span>
                <a href="<?= site_url('admin/posts') ?>" class="small text-decoration-none">Tout voir →</a>
            </div>
            <ul class="list-group list-group-flush">
                <?php if ($recentPosts === []) : ?>
                    <li class="list-group-item text-muted small py-3">Aucun article.</li>
                <?php else : ?>
                    <?php foreach ($recentPosts as $post) :
                        $pid = (int) ($post['id'] ?? 0);
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-2 py-2">
                            <div class="min-w-0 flex-grow-1">
                                <a href="<?= site_url('admin/posts/edit/' . $pid) ?>" class="fw-medium text-decoration-none text-truncate d-block"><?= esc((string) ($post['title'] ?? '')) ?></a>
                                <span class="small text-muted"><code class="small"><?= esc((string) ($post['slug'] ?? '')) ?></code> · <?= admin_format_datetime($post['updated_at'] ?? null) ?></span>
                            </div>
                            <?php if (($post['status'] ?? '') === 'published') : ?>
                                <span class="badge text-bg-success align-self-center flex-shrink-0">Publié</span>
                            <?php else : ?>
                                <span class="badge text-bg-warning text-dark align-self-center flex-shrink-0">Brouillon</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<h2 class="h6 text-muted text-uppercase mb-3">Raccourcis</h2>
<ul class="list-unstyled row g-3">
    <li class="col-md-6 col-lg-4">
        <a class="d-block p-3 border rounded bg-white shadow-sm text-decoration-none text-dark h-100" href="<?= site_url('admin/pages') ?>">
            <strong>Pages</strong>
            <span class="d-block small text-muted mt-1">Contenus statiques (à propos, contact…)</span>
        </a>
    </li>
    <li class="col-md-6 col-lg-4">
        <a class="d-block p-3 border rounded bg-white shadow-sm text-decoration-none text-dark h-100" href="<?= site_url('admin/posts') ?>">
            <strong>Presse</strong>
            <span class="d-block small text-muted mt-1">Articles et communiqués</span>
        </a>
    </li>
    <li class="col-md-6 col-lg-4">
        <a class="d-block p-3 border rounded bg-white shadow-sm text-decoration-none text-dark h-100" href="<?= site_url('admin/media') ?>">
            <strong>Médias</strong>
            <span class="d-block small text-muted mt-1">Images et PDF (glisser-déposer)</span>
        </a>
    </li>
    <li class="col-md-6 col-lg-4">
        <a class="d-block p-3 border rounded bg-white shadow-sm text-decoration-none text-dark h-100" href="<?= site_url('admin/volunteers') ?>">
            <strong>Volontaires</strong>
            <span class="d-block small text-muted mt-1">Candidatures reçues</span>
        </a>
    </li>
    <li class="col-md-6 col-lg-4">
        <a class="d-block p-3 border rounded bg-white shadow-sm text-decoration-none text-dark h-100" href="<?= site_url('admin/project-contributions') ?>">
            <strong>Financements projets</strong>
            <span class="d-block small text-muted mt-1">Propositions à valider</span>
        </a>
    </li>
</ul>
