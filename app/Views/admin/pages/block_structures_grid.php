<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$layout = strtolower(trim((string) ($b['layout'] ?? 'dept')));
if (! in_array($layout, ['hub', 'dept'], true)) {
    $layout = 'dept';
}
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_structures_grid')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="structures_grid">
        <div class="mb-3">
            <label class="form-label small mb-1"><?= esc(lang('Admin.cms_structures_layout')) ?></label>
            <select name="<?= esc($pfx, 'attr') ?>[layout]" class="form-select form-select-sm" style="max-width: 20rem;">
                <option value="dept" <?= $layout === 'dept' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_structures_layout_dept')) ?></option>
                <option value="hub" <?= $layout === 'hub' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_structures_layout_hub')) ?></option>
            </select>
        </div>

        <?= view('admin/pages/partials/block_section_intro_fields', [
            'pfx'                  => $pfx,
            'block'                => $b,
            'kickerPlaceholder'    => lang('Admin.cms_structures_kicker_placeholder'),
            'headingIdPlaceholder' => 'structure-heading',
        ]) ?>

        <?= view('admin/pages/partials/block_teal_banner_fields', [
            'pfx'                      => $pfx,
            'block'                    => $b,
            'helpLangKey'              => 'Admin.cms_structures_banner_help',
            'titlePlaceholderLang'     => 'Admin.cms_structures_banner_title_placeholder',
            'subtitlePlaceholderLang'  => 'Admin.cms_structures_banner_subtitle_placeholder',
        ]) ?>

        <div class="form-text mb-2"><?= lang('Admin.cms_structures_grid_help', [site_url('admin/structures')]) ?></div>
        <a href="<?= site_url('admin/structures') ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_manage_structures')) ?></a>
    </div>
</div>
