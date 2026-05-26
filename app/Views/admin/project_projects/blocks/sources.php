<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$lines = admin_pp_repeat_scalar_lines(is_array($b['lines'] ?? null) ? $b['lines'] : []);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary project-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.block_type_sources')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="sources">
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_section_title')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="lines">
            <p class="small text-muted mb-1"><?= esc(lang('Admin.block_sources_line_help')) ?></p>
            <div class="pp-repeat-body">
            <?php foreach ($lines as $li => $line) : ?>
                <?= view('admin/project_projects/blocks/sources_line_row', [
                    'name'  => $pfx . '[lines][' . $li . ']',
                    'value' => is_string($line) ? $line : '',
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2"><?= esc(lang('Admin.block_add_line')) ?></button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/sources_line_row', [
                    'name'  => $pfx . '[lines][__RI__]',
                    'value' => '',
                ]) ?>
            </template>
        </div>
    </div>
</div>
