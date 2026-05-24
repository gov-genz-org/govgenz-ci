<?php

declare(strict_types=1);

/** @var string $rp */
/** @var array<string, string> $row */
$row = $row ?? ['item' => '', 'quantity' => '', 'notes' => ''];
?>
<div class="pp-repeat-row row g-2 align-items-md-center mb-2">
    <div class="col-12 col-md-5">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_item')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[item]" class="form-control form-control-sm" value="<?= esc((string) ($row['item'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_item_example'), 'attr') ?>" maxlength="255">
    </div>
    <div class="col-12 col-md-2">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_qty')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[quantity]" class="form-control form-control-sm" value="<?= esc((string) ($row['quantity'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_qty_example'), 'attr') ?>" maxlength="64">
    </div>
    <div class="col-12 col-md">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_details')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[notes]" class="form-control form-control-sm" value="<?= esc((string) ($row['notes'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_notes_example'), 'attr') ?>" maxlength="500">
    </div>
    <div class="col-auto d-flex align-items-center justify-content-end ms-md-auto">
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?>
    </div>
</div>
