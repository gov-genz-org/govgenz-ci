<?php

declare(strict_types=1);

$path  = trim(service('request')->getPath(), '/');
$parts = $path === '' ? [] : explode('/', $path);
$section = '';
if (($parts[0] ?? '') === 'admin') {
    $section = $parts[1] ?? 'dashboard';
}

$dashboardActive   = $section === 'dashboard';
$pagesActive       = $section === 'pages';
$cmsGuideActive    = $section === 'cms-guide';
$siteMenuActive    = $section === 'site-menu';
$postsActive       = $section === 'posts';
$mediaActive      = $section === 'media';
$volunteersActive = $section === 'volunteers';
$sectorsActive    = $section === 'sectors';
$loginEventsActive = $section === 'login-events';
$staffUsersActive  = $section === 'staff-users';
$isStaffAdmin      = session()->get('staff_role') === 'admin';
?>
<nav class="admin-sidebar-nav d-flex flex-column" aria-label="Navigation administration">
    <a class="nav-link rounded px-3 py-2 <?= $dashboardActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin') ?>">Tableau de bord</a>

    <hr class="admin-sidebar-rule">

    <div class="admin-sidebar-section">
        <p class="admin-sidebar-section-label" role="presentation">Contenu</p>
        <div class="admin-sidebar-items d-flex flex-column gap-1">
            <a class="nav-link rounded px-3 py-2 <?= $siteMenuActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/site-menu') ?>">Menu du site</a>
            <a class="nav-link rounded px-3 py-2 <?= $pagesActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/pages') ?>">Pages</a>
            <a class="nav-link rounded px-3 py-2 <?= $cmsGuideActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/cms-guide') ?>">Blocs HTML (aide)</a>
            <a class="nav-link rounded px-3 py-2 <?= $postsActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/posts') ?>">Presse</a>
            <a class="nav-link rounded px-3 py-2 <?= $mediaActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/media') ?>">Médias</a>
        </div>
    </div>

    <hr class="admin-sidebar-rule">

    <div class="admin-sidebar-section">
        <p class="admin-sidebar-section-label" role="presentation">Données</p>
        <div class="admin-sidebar-items d-flex flex-column gap-1">
            <a class="nav-link rounded px-3 py-2 <?= $volunteersActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/volunteers') ?>">Volontaires</a>
            <a class="nav-link rounded px-3 py-2 <?= $sectorsActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/sectors') ?>">Secteurs</a>
        </div>
    </div>

    <?php if ($isStaffAdmin) : ?>
        <hr class="admin-sidebar-rule">

        <div class="admin-sidebar-section">
            <p class="admin-sidebar-section-label" role="presentation">Administration</p>
            <div class="admin-sidebar-items d-flex flex-column gap-1">
                <a class="nav-link rounded px-3 py-2 <?= $loginEventsActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/login-events') ?>">Journal connexion</a>
                <a class="nav-link rounded px-3 py-2 <?= $staffUsersActive ? 'active bg-dark text-white' : 'text-dark' ?>" href="<?= site_url('admin/staff-users') ?>">Équipe</a>
            </div>
        </div>
    <?php endif; ?>
</nav>
