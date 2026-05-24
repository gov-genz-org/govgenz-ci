<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?= base_url('assets/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo-256.png') ?>">
    <title><?= esc($title ?? 'Admin') ?> — GovGenZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/govgenz-tokens.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/govgenz-admin.css') ?>">
    <?= $extraHead ?? '' ?>
</head>
<body class="<?= session()->get('staff_user_id') ? 'bg-light' : esc($bodyClass ?? 'admin-login-shell min-vh-100 d-flex flex-column') ?>">
<?php $adminToastSuccess = session()->getFlashdata('message'); ?>
<?php if (session()->get('staff_user_id')) : ?>
<nav class="navbar navbar-expand navbar-dark admin-navbar border-bottom sticky-top">
    <div class="container-fluid px-3">
        <div class="d-flex align-items-center gap-2 flex-wrap flex-grow-1 min-w-0 me-2">
            <a class="navbar-brand fw-semibold mb-0" href="<?= site_url('admin') ?>">GovGenZ Admin</a>
            <a class="btn btn-outline-light btn-sm px-2 py-1 rounded-pill" href="<?= site_url('/') ?>" target="_blank" rel="noopener noreferrer" title="<?= esc(lang('Admin.ui_view_site_title'), 'attr') ?>"><?= esc(lang('Admin.ui_view_site')) ?></a>
        </div>
        <div class="navbar-nav ms-auto flex-row gap-2 gap-lg-3 align-items-center flex-shrink-0">
            <span class="navbar-text small text-white-50 d-none d-md-inline text-truncate" style="max-width:14rem"><?= esc(session()->get('staff_email') ?? '') ?></span>
            <?php $staffRole = session()->get('staff_role'); ?>
            <?php if (is_string($staffRole) && $staffRole !== '') : ?>
                <span class="badge rounded-pill text-bg-secondary align-middle text-uppercase small"><?= esc($staffRole) ?></span>
            <?php endif; ?>
            <form action="<?= site_url('admin/logout') ?>" method="post" class="d-inline-flex align-items-center">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-link nav-link text-warning py-1 px-2 border-0 text-decoration-none"><?= esc(lang('Admin.ui_logout')) ?></button>
            </form>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row g-0">
        <aside class="col-lg-2 border-end bg-white min-vh-100 py-4 px-2 px-lg-3 admin-sidebar">
            <?= view('admin/partials/sidebar') ?>
        </aside>
        <main id="main-content" class="col-lg-10 py-4 px-3 px-lg-4" tabindex="-1">
            <?= view('admin/partials/breadcrumbs') ?>
            <?= view('admin/partials/alerts') ?>
            <?= $main ?? '' ?>
        </main>
    </div>
</div>
<?php else : ?>
<div class="flex-grow-1 d-flex align-items-center justify-content-center px-3 py-5">
    <?= view('admin/partials/alerts') ?>
    <?= $main ?? '' ?>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (session()->get('staff_user_id')) : ?>
<?= view('admin/partials/confirm_modal') ?>
<script defer src="<?= base_url('js/admin/datetime-client.js') ?>"></script>
<?php endif; ?>
<?= $extraScripts ?? '' ?>
<?php if (! empty($adminToastSuccess)) : ?>
<?= view('admin/partials/flash_toast', ['message' => $adminToastSuccess]) ?>
<?php endif; ?>
</body>
</html>
