<?php

declare(strict_types=1);

/** @var string $name */
/** @var string $value */
?>
<div class="pp-repeat-row d-flex gap-2 align-items-center">
    <input type="text" name="<?= esc($name, 'attr') ?>" class="form-control form-control-sm flex-grow-1" value="<?= esc($value) ?>" maxlength="500" placeholder="<?= esc(lang('Admin.ph_block_bullet'), 'attr') ?>">
    <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_bullet')]) ?>
</div>
