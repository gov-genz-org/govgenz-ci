<?php

declare(strict_types=1);

/** @var string $name */
/** @var array<string, mixed> $row */

$row = is_array($row ?? null) ? $row : [];
?>
<div class="cms-repeat-row row g-2 align-items-center mb-2">
    <div class="col-md-3"><input type="text" name="<?= esc($name, 'attr') ?>[value]" class="form-control form-control-sm" value="<?= esc((string) ($row['value'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_cms_metrics_value'), 'attr') ?>"></div>
    <div class="col-md-2"><input type="text" name="<?= esc($name, 'attr') ?>[suffix]" class="form-control form-control-sm" value="<?= esc((string) ($row['suffix'] ?? '')) ?>" placeholder="%"></div>
    <div class="col-md-6"><input type="text" name="<?= esc($name, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($row['label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_label'), 'attr') ?>"></div>
    <div class="col-md-1 d-flex justify-content-md-end"><?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?></div>
</div>
