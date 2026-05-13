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
    ['label' => 'Tableau de bord', 'url' => site_url('admin')],
];

if ($rest === 'pages') {
    $items[] = ['label' => 'Pages', 'url' => null];
} elseif ($rest === 'pages/create') {
    $items[] = ['label' => 'Pages', 'url' => site_url('admin/pages')];
    $items[] = ['label' => 'Nouvelle page', 'url' => null];
} elseif (preg_match('#^pages/edit/\d+$#', $rest)) {
    $items[] = ['label' => 'Pages', 'url' => site_url('admin/pages')];
    $items[] = ['label' => 'Éditer une page', 'url' => null];
} elseif ($rest === 'site-menu') {
    $items[] = ['label' => 'Menu du site', 'url' => null];
} elseif ($rest === 'site-menu/create') {
    $items[] = ['label' => 'Menu du site', 'url' => site_url('admin/site-menu')];
    $items[] = ['label' => 'Nouvelle entrée', 'url' => null];
} elseif (preg_match('#^site-menu/edit/\d+$#', $rest)) {
    $items[] = ['label' => 'Menu du site', 'url' => site_url('admin/site-menu')];
    $items[] = ['label' => 'Éditer', 'url' => null];
} elseif ($rest === 'cms-guide') {
    $items[] = ['label' => 'Blocs HTML (aide)', 'url' => null];
} elseif ($rest === 'posts') {
    $items[] = ['label' => 'Presse', 'url' => null];
} elseif ($rest === 'posts/create') {
    $items[] = ['label' => 'Presse', 'url' => site_url('admin/posts')];
    $items[] = ['label' => 'Nouvel article', 'url' => null];
} elseif (preg_match('#^posts/edit/\d+$#', $rest)) {
    $items[] = ['label' => 'Presse', 'url' => site_url('admin/posts')];
    $items[] = ['label' => 'Éditer un article', 'url' => null];
} elseif ($rest === 'media') {
    $items[] = ['label' => 'Médias', 'url' => null];
} elseif ($rest === 'volunteers') {
    $items[] = ['label' => 'Volontaires', 'url' => null];
} elseif ($rest === 'sectors') {
    $items[] = ['label' => 'Secteurs', 'url' => null];
} elseif ($rest === 'sectors/create') {
    $items[] = ['label' => 'Secteurs', 'url' => site_url('admin/sectors')];
    $items[] = ['label' => 'Nouveau secteur', 'url' => null];
} elseif (preg_match('#^sectors/edit/\d+$#', $rest)) {
    $items[] = ['label' => 'Secteurs', 'url' => site_url('admin/sectors')];
    $items[] = ['label' => 'Modifier', 'url' => null];
} else {
    return;
}
?>
<nav aria-label="Fil d’Ariane" class="mb-3">
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
