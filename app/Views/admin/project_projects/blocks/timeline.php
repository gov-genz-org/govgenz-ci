<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$phases = admin_pp_repeat_object_rows(
    is_array($b['phases'] ?? null) ? $b['phases'] : [],
    static fn (array $ph): bool => trim((string) ($ph['phase_label'] ?? '')) === ''
        && trim((string) ($ph['duration'] ?? '')) === ''
        && trim((string) ($ph['step_title'] ?? '')) === ''
        && trim((string) ($ph['body'] ?? '')) === '',
    ['phase_label' => '', 'duration' => '', 'step_title' => '', 'body' => ''],
);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary project-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.block_type_timeline')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="timeline">
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_section_title')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_timeline_title'), 'attr') ?>">
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="phases">
            <div class="pp-repeat-body">
            <?php foreach ($phases as $pi => $ph) : ?>
                <?= view('admin/project_projects/blocks/timeline_phase_row', [
                    'rp' => $pfx . '[phases][' . $pi . ']',
                    'ph' => is_array($ph) ? $ph : [],
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2"><?= esc(lang('Admin.block_add_phase')) ?></button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/timeline_phase_row', [
                    'rp' => $pfx . '[phases][__RI__]',
                    'ph' => ['phase_label' => '', 'duration' => '', 'step_title' => '', 'body' => ''],
                ]) ?>
            </template>
        </div>
    </div>
</div>
