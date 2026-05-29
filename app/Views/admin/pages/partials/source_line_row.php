<?php

declare(strict_types=1);

/** @var string $name */
/** @var string $value */
?>
<div class="cms-repeat-row d-flex gap-2 align-items-center mb-2">
    <input type="text" name="<?= esc($name, 'attr') ?>" class="form-control form-control-sm flex-grow-1" value="<?= esc($value) ?>" placeholder="<?= esc(lang('Admin.cms_source_line'), 'attr') ?>">
    <?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?>
</div>
