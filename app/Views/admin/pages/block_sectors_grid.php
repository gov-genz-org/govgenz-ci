<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$layout = strtolower(trim((string) ($b['layout'] ?? 'compact')));
if ($layout === 'tile' || $layout === 'card') {
    $layout = 'compact';
}
if (! in_array($layout, ['compact', 'wide'], true)) {
    $layout = 'compact';
}
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_sectors_grid')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="sectors_grid">
        <div class="mb-3">
            <label class="form-label small mb-1"><?= esc(lang('Admin.cms_sectors_layout')) ?></label>
            <select name="<?= esc($pfx, 'attr') ?>[layout]" class="form-select form-select-sm" style="max-width: 20rem;">
                <option value="compact" <?= $layout === 'compact' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_sectors_layout_compact')) ?></option>
                <option value="wide" <?= $layout === 'wide' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_sectors_layout_wide')) ?></option>
            </select>
        </div>

        <?= view('admin/pages/partials/block_section_intro_fields', [
            'pfx'                  => $pfx,
            'block'                => $b,
            'kickerPlaceholder'    => lang('Admin.cms_sectors_kicker_placeholder'),
            'headingIdPlaceholder' => 'secteurs-heading',
        ]) ?>

        <?= view('admin/pages/partials/block_teal_banner_fields', [
            'pfx'                     => $pfx,
            'block'                   => $b,
            'helpLangKey'             => 'Admin.cms_sectors_banner_help',
            'titlePlaceholderLang'    => 'Admin.cms_sectors_banner_title_placeholder',
            'subtitlePlaceholderLang' => 'Admin.cms_sectors_banner_subtitle_placeholder',
        ]) ?>

        <div class="form-text mb-2"><?= lang('Admin.cms_sectors_grid_help', [site_url('admin/sectors')]) ?></div>
        <a href="<?= site_url('admin/sectors') ?>" class="btn btn-sm btn-outline-secondary"><?= esc(lang('Admin.action_manage_sectors')) ?></a>
    </div>
</div>
