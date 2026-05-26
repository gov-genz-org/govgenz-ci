<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
if (! in_array($style, ['default', 'warm', 'teal'], true)) {
    $style = 'teal';
}
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary project-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.block_type_note')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="note_panel">
        <div class="row g-2 mb-2">
            <div class="col-md-8">
                <label class="form-label small"><?= esc(lang('Admin.block_section_title')) ?></label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small"><?= esc(lang('Admin.block_style_title_short')) ?></label>
                <select name="<?= esc($pfx, 'attr') ?>[heading_style]" class="form-select form-select-sm">
                    <option value="default" <?= $style === 'default' ? 'selected' : '' ?>><?= esc(lang('Admin.block_style_default')) ?></option>
                    <option value="warm" <?= $style === 'warm' ? 'selected' : '' ?>><?= esc(lang('Admin.block_style_warm')) ?></option>
                    <option value="teal" <?= $style === 'teal' ? 'selected' : '' ?>><?= esc(lang('Admin.block_style_teal')) ?></option>
                </select>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_note_main')) ?></label>
            <textarea name="<?= esc($pfx, 'attr') ?>[message]" class="form-control form-control-sm" rows="3" maxlength="2000"><?= esc((string) ($b['message'] ?? '')) ?></textarea>
        </div>
        <div class="mb-0">
            <label class="form-label small"><?= esc(lang('Admin.block_note_sub')) ?></label>
            <textarea name="<?= esc($pfx, 'attr') ?>[submessage]" class="form-control form-control-sm" rows="2" maxlength="1000"><?= esc((string) ($b['submessage'] ?? '')) ?></textarea>
        </div>
    </div>
</div>
