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
$cmsBlocksGuideActive = $section === 'cms-guide-blocks';
$siteMenuActive    = $section === 'site-menu';
$postsActive       = $section === 'posts';
$mediaActive      = $section === 'media';
$volunteersActive = $section === 'volunteers';
$projectContributionsActive = $section === 'project-contributions';
$sectorsActive    = $section === 'sectors';
$projectProjectsActive = $section === 'project-projects';
$positionItemsActive = $section === 'position-items';
$projectExchangeRatesActive = $section === 'project-exchange-rates';
$loginEventsActive = $section === 'login-events';
$staffUsersActive  = $section === 'staff-users';
$isStaffAdmin      = session()->get('staff_role') === 'admin';
?>
<nav class="admin-sidebar-nav d-flex flex-column" aria-label="<?= esc(lang('Admin.nav_section_admin')) ?>">
    <a class="nav-link rounded px-3 py-2 <?= $dashboardActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin') ?>"><?= esc(lang('Admin.nav_dashboard')) ?></a>

    <hr class="admin-sidebar-rule">

    <div class="admin-sidebar-section">
        <p class="admin-sidebar-section-label" role="presentation"><?= esc(lang('Admin.nav_section_content')) ?></p>
        <div class="admin-sidebar-items d-flex flex-column gap-1">
            <a class="nav-link rounded px-3 py-2 <?= $siteMenuActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/site-menu') ?>"><?= esc(lang('Admin.nav_site_menu')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $pagesActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/pages') ?>"><?= esc(lang('Admin.nav_pages')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $cmsGuideActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/cms-guide') ?>"><?= esc(lang('Admin.nav_cms_guide')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $cmsBlocksGuideActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/cms-guide-blocks') ?>"><?= esc(lang('Admin.nav_cms_blocks_guide')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $postsActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/posts') ?>"><?= esc(lang('Admin.nav_posts')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $mediaActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/media') ?>"><?= esc(lang('Admin.nav_media')) ?></a>
        </div>
    </div>

    <hr class="admin-sidebar-rule">

    <div class="admin-sidebar-section">
        <p class="admin-sidebar-section-label" role="presentation"><?= esc(lang('Admin.nav_section_data')) ?></p>
        <div class="admin-sidebar-items d-flex flex-column gap-1">
            <a class="nav-link rounded px-3 py-2 <?= $volunteersActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/volunteers') ?>"><?= esc(lang('Admin.nav_volunteers')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $sectorsActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/sectors') ?>"><?= esc(lang('Admin.nav_sectors')) ?></a>
        </div>
    </div>

    <hr class="admin-sidebar-rule">

    <div class="admin-sidebar-section">
        <p class="admin-sidebar-section-label" role="presentation"><?= esc(lang('Admin.nav_section_projects')) ?></p>
        <div class="admin-sidebar-items d-flex flex-column gap-1">
            <a class="nav-link rounded px-3 py-2 <?= $projectContributionsActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/project-contributions') ?>"><?= esc(lang('Admin.nav_project_contributions')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $projectProjectsActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/project-projects') ?>"><?= esc(lang('Admin.nav_project_projects')) ?></a>
            <a class="nav-link rounded px-3 py-2 <?= $projectExchangeRatesActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/project-exchange-rates') ?>"><?= esc(lang('Admin.nav_exchange_rates')) ?></a>
        </div>
    </div>

    <hr class="admin-sidebar-rule">

    <div class="admin-sidebar-section">
        <p class="admin-sidebar-section-label" role="presentation"><?= esc(lang('Admin.nav_section_positions')) ?></p>
        <div class="admin-sidebar-items d-flex flex-column gap-1">
            <a class="nav-link rounded px-3 py-2 <?= $positionItemsActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/position-items') ?>"><?= esc(lang('Admin.nav_position_items')) ?></a>
        </div>
    </div>

    <?php if ($isStaffAdmin) : ?>
        <hr class="admin-sidebar-rule">

        <div class="admin-sidebar-section">
            <p class="admin-sidebar-section-label" role="presentation"><?= esc(lang('Admin.nav_section_admin')) ?></p>
            <div class="admin-sidebar-items d-flex flex-column gap-1">
                <a class="nav-link rounded px-3 py-2 <?= $loginEventsActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/login-events') ?>"><?= esc(lang('Admin.nav_login_events')) ?></a>
                <a class="nav-link rounded px-3 py-2 <?= $staffUsersActive ? 'active' : 'text-dark' ?>" href="<?= site_url('admin/staff-users') ?>"><?= esc(lang('Admin.nav_staff_users')) ?></a>
            </div>
        </div>
    <?php endif; ?>
</nav>
