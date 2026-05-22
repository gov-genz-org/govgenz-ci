<?php

declare(strict_types=1);

/** @var string $rp */
/** @var array<string, string> $ph */
$ph = $ph ?? ['phase_label' => '', 'duration' => '', 'step_title' => '', 'body' => ''];
?>
<div class="pp-repeat-row border rounded p-2 mb-2 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <p class="small fw-semibold mb-0"><?= esc(lang('Admin.block_phase_heading')) ?></p>
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_phase')]) ?>
    </div>
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label small"><?= esc(lang('Admin.block_phase_label')) ?></label>
            <input type="text" name="<?= esc($rp, 'attr') ?>[phase_label]" class="form-control form-control-sm" value="<?= esc((string) ($ph['phase_label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_phase'), 'attr') ?>">
        </div>
        <div class="col-md-8">
            <label class="form-label small"><?= esc(lang('Admin.block_phase_duration')) ?></label>
            <input type="text" name="<?= esc($rp, 'attr') ?>[duration]" class="form-control form-control-sm" value="<?= esc((string) ($ph['duration'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_duration'), 'attr') ?>">
        </div>
        <div class="col-12">
            <label class="form-label small"><?= esc(lang('Admin.block_phase_step_title')) ?></label>
            <input type="text" name="<?= esc($rp, 'attr') ?>[step_title]" class="form-control form-control-sm" value="<?= esc((string) ($ph['step_title'] ?? '')) ?>">
        </div>
        <div class="col-12">
            <label class="form-label small"><?= esc(lang('Admin.block_description')) ?></label>
            <textarea name="<?= esc($rp, 'attr') ?>[body]" class="form-control form-control-sm" rows="2" maxlength="4000"><?= esc((string) ($ph['body'] ?? '')) ?></textarea>
        </div>
    </div>
</div>
