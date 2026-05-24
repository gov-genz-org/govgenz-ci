<?php

declare(strict_types=1);

/** @var string $rp */
/** @var array<string, mixed> $row */
$row = is_array($row ?? null) ? $row : [];
$pct = (int) ($row['bar_percent'] ?? 0);
if ($pct < 0) {
    $pct = 0;
}
if ($pct > 100) {
    $pct = 100;
}
?>
<div class="pp-repeat-row row g-2 align-items-md-center mb-2">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_label')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($row['label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_label'), 'attr') ?>">
    </div>
    <div class="col-12 col-md">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_figures')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[numbers]" class="form-control form-control-sm" value="<?= esc((string) ($row['numbers'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_impact_numbers'), 'attr') ?>">
    </div>
    <div class="col-12 col-md-2">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_bar')) ?></label>
        <input type="number" name="<?= esc($rp, 'attr') ?>[bar_percent]" class="form-control form-control-sm" min="0" max="100" value="<?= esc((string) $pct) ?>" placeholder="%">
    </div>
    <div class="col-auto d-flex align-items-center justify-content-end ms-md-auto">
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?>
    </div>
</div>
