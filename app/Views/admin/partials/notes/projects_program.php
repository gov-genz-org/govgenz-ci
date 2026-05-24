<?php

declare(strict_types=1);

helper('admin');
?>
<div class="alert alert-light border small mb-3" role="note">
    <?= lang('Admin.note_projects_program_body') ?>
    <p class="mb-0 d-flex flex-wrap gap-3 align-items-center">
        <a href="<?= site_url('admin/pages') ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_cms_pages')) ?></a>
        <a href="<?= esc(admin_public_projects_program_list_url('fr'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_fr')) ?></a>
        <a href="<?= esc(admin_public_projects_program_list_url('en'), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><?= esc(lang('Admin.action_view_public_list_en')) ?></a>
    </p>
</div>
