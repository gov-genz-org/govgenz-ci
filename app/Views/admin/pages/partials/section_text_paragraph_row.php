<?php

declare(strict_types=1);

/** @var string $name */
/** @var string $value */
/** @var int $n */
?>
<div class="cms-repeat-row border rounded p-2 bg-light-subtle">
    <div class="d-flex gap-2 align-items-start">
        <div class="flex-grow-1 min-w-0">
            <label class="form-label small mb-1"><?= esc((int) $n > 0 ? lang('Admin.block_extra_paragraph_n', [(int) $n]) : lang('Admin.block_extra_paragraph')) ?></label>
            <textarea name="<?= esc($name, 'attr') ?>" class="form-control form-control-sm" rows="2"><?= esc($value) ?></textarea>
        </div>
        <?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_paragraph')]) ?>
    </div>
</div>
