<?php

declare(strict_types=1);

$path = trim((string) service('request')->getUri()->getPath(), '/');
if ($path === '' || ! str_starts_with($path, 'admin')) {
    return;
}

$rest = ltrim(substr($path, strlen('admin')), '/');

if ($rest === '') {
    return;
}

$items = [
    ['label' => lang('Admin.nav_dashboard'), 'url' => site_url('admin')],
];

if ($rest === 'pages') {
    $items[] = ['label' => lang('Admin.nav_pages'), 'url' => null];
} elseif ($rest === 'pages/create') {
    $items[] = ['label' => lang('Admin.nav_pages'), 'url' => site_url('admin/pages')];
    $items[] = ['label' => lang('Admin.breadcrumb_page_new'), 'url' => null];
} elseif (preg_match('#^pages/edit/\d+$#', $rest)) {
    $items[] = ['label' => lang('Admin.nav_pages'), 'url' => site_url('admin/pages')];
    $items[] = ['label' => lang('Admin.breadcrumb_page_edit'), 'url' => null];
} elseif ($rest === 'site-menu') {
    $items[] = ['label' => lang('Admin.nav_site_menu'), 'url' => null];
} elseif ($rest === 'site-menu/create') {
    $items[] = ['label' => lang('Admin.nav_site_menu'), 'url' => site_url('admin/site-menu')];
    $items[] = ['label' => lang('Admin.breadcrumb_menu_new'), 'url' => null];
} elseif (preg_match('#^site-menu/edit/\d+$#', $rest)) {
    $items[] = ['label' => lang('Admin.nav_site_menu'), 'url' => site_url('admin/site-menu')];
    $items[] = ['label' => lang('Admin.breadcrumb_menu_edit'), 'url' => null];
} elseif ($rest === 'cms-guide') {
    $items[] = ['label' => lang('Admin.nav_cms_guide'), 'url' => null];
} elseif ($rest === 'cms-guide-blocks') {
    $items[] = ['label' => lang('Admin.nav_cms_blocks_guide'), 'url' => null];
} elseif ($rest === 'posts') {
    $items[] = ['label' => lang('Admin.nav_posts'), 'url' => null];
} elseif ($rest === 'posts/create') {
    $items[] = ['label' => lang('Admin.nav_posts'), 'url' => site_url('admin/posts')];
    $items[] = ['label' => lang('Admin.breadcrumb_post_new'), 'url' => null];
} elseif (preg_match('#^posts/edit/\d+$#', $rest)) {
    $items[] = ['label' => lang('Admin.nav_posts'), 'url' => site_url('admin/posts')];
    $items[] = ['label' => lang('Admin.breadcrumb_post_edit'), 'url' => null];
} elseif ($rest === 'media') {
    $items[] = ['label' => lang('Admin.nav_media'), 'url' => null];
} elseif ($rest === 'volunteers') {
    $items[] = ['label' => lang('Admin.nav_volunteers'), 'url' => null];
} elseif ($rest === 'project-contributions') {
    $items[] = ['label' => lang('Admin.nav_project_contributions'), 'url' => null];
} elseif ($rest === 'sectors') {
    $items[] = ['label' => lang('Admin.nav_sectors'), 'url' => null];
} elseif ($rest === 'sectors/create') {
    $items[] = ['label' => lang('Admin.nav_sectors'), 'url' => site_url('admin/sectors')];
    $items[] = ['label' => lang('Admin.breadcrumb_sector_new'), 'url' => null];
} elseif (preg_match('#^sectors/edit/\d+$#', $rest)) {
    $items[] = ['label' => lang('Admin.nav_sectors'), 'url' => site_url('admin/sectors')];
    $items[] = ['label' => lang('Admin.breadcrumb_edit'), 'url' => null];
} elseif ($rest === 'project-projects') {
    $items[] = ['label' => lang('Admin.nav_project_projects'), 'url' => null];
} elseif ($rest === 'project-projects/create') {
    $items[] = ['label' => lang('Admin.nav_project_projects'), 'url' => site_url('admin/project-projects')];
    $items[] = ['label' => lang('Admin.breadcrumb_project_new'), 'url' => null];
} elseif (preg_match('#^project-projects/edit/\d+$#', $rest)) {
    $items[] = ['label' => lang('Admin.nav_project_projects'), 'url' => site_url('admin/project-projects')];
    $items[] = ['label' => lang('Admin.breadcrumb_edit'), 'url' => null];
} elseif ($rest === 'project-exchange-rates') {
    $items[] = ['label' => lang('Admin.nav_exchange_rates'), 'url' => null];
} elseif ($rest === 'position-items') {
    $items[] = ['label' => lang('Admin.nav_position_items'), 'url' => null];
} elseif ($rest === 'position-items/create') {
    $items[] = ['label' => lang('Admin.nav_position_items'), 'url' => site_url('admin/position-items')];
    $items[] = ['label' => lang('Admin.breadcrumb_position_new'), 'url' => null];
} elseif (preg_match('#^position-items/edit/\d+$#', $rest)) {
    $items[] = ['label' => lang('Admin.nav_position_items'), 'url' => site_url('admin/position-items')];
    $items[] = ['label' => lang('Admin.breadcrumb_edit'), 'url' => null];
} else {
    return;
}
?>
<nav aria-label="<?= esc(lang('Admin.breadcrumb_aria')) ?>" class="mb-3">
    <ol class="breadcrumb small mb-0 bg-white rounded border px-3 py-2 shadow-sm">
        <?php foreach ($items as $idx => $row) : ?>
            <?php $isLast = $idx === count($items) - 1; ?>
            <?php if ($isLast) : ?>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($row['label']) ?></li>
            <?php else : ?>
                <li class="breadcrumb-item"><a href="<?= esc($row['url']) ?>" class="text-decoration-none"><?= esc($row['label']) ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
