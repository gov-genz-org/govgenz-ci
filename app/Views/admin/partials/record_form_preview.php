<?php

declare(strict_types=1);

/** @var int $recordId */
/** @var string $draftPreviewPath ex. admin/project-projects/preview-draft */
/** @var string $savedPreviewPath ex. admin/project-projects/preview */
?>
<div class="alert alert-secondary border py-2 small mb-3">
    <p class="mb-2"><strong><?= esc(lang('Admin.label_preview')) ?></strong></p>
    <ul class="mb-3 ps-3">
        <li><?= lang('Admin.help_page_preview_draft') ?></li>
        <li><?= lang('Admin.help_page_preview_saved') ?></li>
    </ul>
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <button type="submit" class="btn btn-sm btn-primary" formaction="<?= site_url($draftPreviewPath . '/' . $recordId) ?>" formmethod="post" formtarget="_blank">
            <?= esc(lang('Admin.action_preview_draft')) ?>
        </button>
        <a href="<?= site_url($savedPreviewPath . '/' . $recordId) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark"><?= esc(lang('Admin.action_preview_saved')) ?></a>
    </div>
</div>
