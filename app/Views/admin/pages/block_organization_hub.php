<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$items = $b['items'] ?? [];
if (! is_array($items)) {
    $items = [];
}
$items = array_values(array_filter($items, static function ($item): bool {
    if (! is_array($item)) {
        return false;
    }

    return trim((string) ($item['name'] ?? '') . (string) ($item['subtitle'] ?? '') . (string) ($item['href'] ?? '')) !== '';
}));
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_organization_hub')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="organization_hub">
        <p class="small fw-semibold mb-2"><?= esc(lang('Admin.cms_hub_core_heading')) ?></p>
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="text" name="<?= esc($pfx, 'attr') ?>[core_label]" class="form-control form-control-sm" value="<?= esc((string) ($b['core_label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_hub_core_label'), 'attr') ?>"></div>
            <div class="col-md-4"><input type="text" name="<?= esc($pfx, 'attr') ?>[core_subtitle]" class="form-control form-control-sm" value="<?= esc((string) ($b['core_subtitle'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_subtitle'), 'attr') ?>"></div>
            <div class="col-md-4"><input type="text" name="<?= esc($pfx, 'attr') ?>[core_href]" class="form-control form-control-sm" value="<?= esc((string) ($b['core_href'] ?? '')) ?>" placeholder="mailto:contact@govgenz.org"></div>
        </div>
        <div class="cms-repeatable" data-cms-repeat-key="items">
            <p class="small fw-semibold mb-2"><?= esc(lang('Admin.cms_hub_items_heading')) ?></p>
            <div class="cms-repeat-body">
            <?php foreach ($items as $ii => $item) : ?>
                <?= view('admin/pages/partials/organization_item_row', [
                    'name' => $pfx . '[items][' . $ii . ']',
                    'item' => is_array($item) ? $item : [],
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_function')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/organization_item_row', [
                    'name' => $pfx . '[items][__RI__]',
                    'item' => [],
                ]) ?>
            </template>
        </div>
    </div>
</div>
