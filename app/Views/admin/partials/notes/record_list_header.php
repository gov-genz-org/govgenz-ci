<?php

declare(strict_types=1);

use App\Libraries\CmsListHeroPageAdmin;

helper('admin');

/** @var 'projects'|'positions'|'press' $listKind */
if ($listKind === 'press') {
    $listUrlFn       = 'admin_public_press_list_url';
    $cmsSlugsNoteKey = 'Admin.note_record_list_cms_slugs_press';
} elseif ($listKind === 'positions') {
    $listUrlFn       = 'admin_public_positions_list_url';
    $cmsSlugsNoteKey = 'Admin.note_record_list_cms_slugs_positions';
} else {
    $listUrlFn       = 'admin_public_projects_list_url';
    $cmsSlugsNoteKey = 'Admin.note_record_list_cms_slugs_projects';
}

$heroFr = CmsListHeroPageAdmin::adminUrlForLocale($listKind, 'fr');
$heroEn = CmsListHeroPageAdmin::adminUrlForLocale($listKind, 'en');
?>
<div class="alert alert-light border small mb-3" role="note">
    <p class="mb-2 text-muted"><?= lang($cmsSlugsNoteKey) ?></p>
    <p class="mb-2 d-flex flex-wrap gap-2 align-items-center">
        <?php if ($heroFr !== null) : ?>
            <a href="<?= esc($heroFr['editUrl'], 'attr') ?>" class="btn btn-sm btn-outline-secondary">
                <?= esc($heroFr['isCreate'] ? lang('Admin.action_create_list_hero_fr') : lang('Admin.action_edit_list_hero_fr')) ?>
            </a>
        <?php endif; ?>
        <?php if ($heroEn !== null) : ?>
            <a href="<?= esc($heroEn['editUrl'], 'attr') ?>" class="btn btn-sm btn-outline-secondary">
                <?= esc($heroEn['isCreate'] ? lang('Admin.action_create_list_hero_en') : lang('Admin.action_edit_list_hero_en')) ?>
            </a>
        <?php endif; ?>
    </p>
    <p class="mb-0 d-flex flex-wrap gap-3 align-items-center">
        <a href="<?= site_url('admin/pages') ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_cms_pages')) ?></a>
        <a href="<?= esc($listUrlFn('fr'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_fr')) ?></a>
        <a href="<?= esc($listUrlFn('en'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_en')) ?></a>
    </p>
</div>
