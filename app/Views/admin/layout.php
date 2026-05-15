<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?= base_url('assets/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo-256.png') ?>">
    <title><?= esc($title ?? 'Admin') ?> — GovGenZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?= $extraHead ?? '' ?>
    <style>
        aside.admin-sidebar .admin-sidebar-nav .nav-link.active {
            font-weight: 600;
        }
        aside.admin-sidebar .admin-sidebar-nav .nav-link:not(.active):hover {
            background: var(--bs-secondary-bg);
        }
        .admin-sidebar-rule {
            border: 0;
            height: 1px;
            background-color: #dee2e6;
            margin: 0.6rem 0.25rem;
            opacity: 1;
        }
        .admin-sidebar-section-label {
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #495057;
            margin: 0 0 0.45rem;
            padding: 0.4rem 0.65rem 0.4rem 0.7rem;
            line-height: 1.25;
            border-radius: 0.3rem;
            background: #e9ecef;
            border-left: 3px solid #0d6efd;
        }
        .admin-sidebar-items .nav-link {
            margin-left: 0.25rem;
            padding-left: 0.85rem !important;
            border-left: 2px solid #dee2e6;
            font-size: 0.9375rem;
        }
        .admin-sidebar-items .nav-link:not(.active):hover {
            border-left-color: #0d6efd;
        }
        .admin-sidebar-items .nav-link.active {
            border-left-color: transparent;
        }
        /* Lisibilité zone travail : texte et données plus nets que le fond gris léger */
        #main-content {
            color: #212529;
            font-size: 1.0625rem;
            line-height: 1.55;
        }
        #main-content h1,
        #main-content .h1,
        #main-content h2,
        #main-content .h2,
        #main-content h3,
        #main-content .h3 {
            color: #121417;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        #main-content .table {
            color: #212529;
            font-size: 1rem;
            --bs-table-striped-bg: rgba(13, 110, 253, 0.045);
            border-color: #dee2e6;
        }
        #main-content .table td,
        #main-content .table th {
            padding-top: 0.7rem;
            padding-bottom: 0.7rem;
        }
        #main-content .table thead th {
            font-weight: 600;
            color: #121417;
            border-bottom-width: 2px;
        }
        #main-content .table tbody td {
            border-color: #e9ecef;
        }
        #main-content .table code {
            font-size: 0.9em;
            color: #084298;
            background-color: rgba(13, 110, 253, 0.09);
            padding: 0.12em 0.4em;
            border-radius: 0.25rem;
        }
        #main-content .admin-sort-link {
            color: inherit;
        }
        #main-content .admin-sort-link:hover {
            color: #0d6efd;
        }
        #main-content .admin-sort-link--active {
            color: #0d6efd;
        }
        #main-content .admin-sort-arrow {
            font-size: 0.65em;
            opacity: 0.85;
            vertical-align: baseline;
        }
        #main-content .form-label {
            font-weight: 600;
            color: #212529;
        }
        #main-content .form-control,
        #main-content .form-select {
            border-color: #c5cdd6;
            font-size: 1rem;
        }
        #main-content .form-control:not(textarea) {
            min-height: calc(1.55em + 0.75rem + 2px);
        }
        #main-content .form-text {
            color: #495057;
            font-size: 0.9375rem;
        }
        #main-content .text-muted {
            color: #495057 !important;
        }
        #main-content .list-group-item {
            color: #212529;
        }
        #main-content .card .card-body {
            color: #212529;
        }
        #main-content .dropzone {
            border-width: 2px !important;
            border-style: dashed !important;
            border-color: #adb5bd !important;
            background: #fff !important;
        }
        .admin-empty {
            border: 2px dashed var(--bs-border-color);
            border-radius: 0.5rem;
            padding: 2rem 1.25rem;
            text-align: center;
            background: var(--bs-white);
            color: #343a40;
        }
        .admin-form-actions {
            position: sticky;
            bottom: 0;
            z-index: 100;
            margin-top: 1.5rem;
            padding: 1rem 0 0.25rem;
            background: linear-gradient(to bottom, transparent, rgba(248, 249, 250, 0.96) 25%);
            border-top: 1px solid var(--bs-border-color);
        }
        main:focus { outline: none; }
        .admin-login-card { max-width: 420px; width: 100%; }
        .admin-login-shell { background: linear-gradient(160deg, #f8f9fa 0%, #e9ecef 45%, #dee2e6 100%); }
        .admin-table-wrap thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #e9ecef;
            box-shadow: inset 0 -2px 0 #dee2e6;
        }
        .admin-table-wrap {
            max-height: min(72vh, 760px);
            overflow: auto;
        }
    </style>
</head>
<body class="<?= session()->get('staff_user_id') ? 'bg-light' : esc($bodyClass ?? 'admin-login-shell min-vh-100 d-flex flex-column') ?>">
<?php $adminToastSuccess = session()->getFlashdata('message'); ?>
<?php if (session()->get('staff_user_id')) : ?>
<nav class="navbar navbar-expand navbar-dark bg-dark border-bottom sticky-top">
    <div class="container-fluid px-3">
        <div class="d-flex align-items-center gap-2 flex-wrap flex-grow-1 min-w-0 me-2">
            <a class="navbar-brand fw-semibold mb-0" href="<?= site_url('admin') ?>">GovGenZ Admin</a>
            <a class="btn btn-outline-light btn-sm px-2 py-1 rounded-pill" href="<?= site_url('/') ?>" target="_blank" rel="noopener noreferrer" title="S’ouvre dans un nouvel onglet">Voir le site</a>
        </div>
        <div class="navbar-nav ms-auto flex-row gap-2 gap-lg-3 align-items-center flex-shrink-0">
            <span class="navbar-text small text-white-50 d-none d-md-inline text-truncate" style="max-width:14rem"><?= esc(session()->get('staff_email') ?? '') ?></span>
            <?php $staffRole = session()->get('staff_role'); ?>
            <?php if (is_string($staffRole) && $staffRole !== '') : ?>
                <span class="badge rounded-pill text-bg-secondary align-middle text-uppercase small"><?= esc($staffRole) ?></span>
            <?php endif; ?>
            <form action="<?= site_url('admin/logout') ?>" method="post" class="d-inline-flex align-items-center">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-link nav-link text-warning py-1 px-2 border-0 text-decoration-none">Déconnexion</button>
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
