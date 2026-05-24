<?php

declare(strict_types=1);

helper('admin');

/** @var 'projects'|'positions' $listKind */
$listUrlFn = $listKind === 'positions'
    ? 'admin_public_positions_list_url'
    : 'admin_public_projects_list_url';
$cmsSlugsNoteKey = $listKind === 'positions'
    ? 'Admin.note_record_list_cms_slugs_positions'
    : 'Admin.note_record_list_cms_slugs_projects';
?>
<div class="alert alert-light border small mb-3" role="note">
    <p class="mb-2 text-muted"><?= lang($cmsSlugsNoteKey) ?></p>
    <p class="mb-0 d-flex flex-wrap gap-3 align-items-center">
        <a href="<?= site_url('admin/pages') ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_cms_pages')) ?></a>
        <a href="<?= esc($listUrlFn('fr'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_fr')) ?></a>
        <a href="<?= esc($listUrlFn('en'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_en')) ?></a>
    </p>
</div>
