<?php

declare(strict_types=1);

/** @var string $name */
/** @var string $value */
/** @var int $n */
?>
<div class="pp-repeat-row border rounded p-3 bg-light">
    <div class="d-flex gap-2 align-items-start">
        <div class="flex-grow-1 min-w-0">
            <label class="form-label small mb-1"><?= esc((int) $n > 0 ? lang('Admin.block_extra_paragraph_n', [(int) $n]) : lang('Admin.block_extra_paragraph')) ?></label>
            <textarea name="<?= esc($name, 'attr') ?>" class="form-control form-control-sm" rows="3" maxlength="4000"><?= esc($value) ?></textarea>
        </div>
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_paragraph'), 'extraClasses' => 'align-self-start']) ?>
    </div>
</div>
