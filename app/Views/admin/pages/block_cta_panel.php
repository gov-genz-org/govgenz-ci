<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$actions = $b['actions'] ?? [];
if (! is_array($actions)) {
    $actions = [];
}
$actions = array_values(array_filter($actions, static function ($action): bool {
    if (! is_array($action)) {
        return false;
    }

    return trim((string) ($action['label'] ?? '') . (string) ($action['href'] ?? '')) !== '';
}));
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_cta_panel')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="cta_panel">
        <textarea name="<?= esc($pfx, 'attr') ?>[text]" class="form-control form-control-sm mb-3" rows="3" placeholder="<?= esc(lang('Admin.cms_cta_text'), 'attr') ?>"><?= esc((string) ($b['text'] ?? '')) ?></textarea>
        <div class="cms-repeatable" data-cms-repeat-key="actions">
            <p class="small fw-semibold mb-2"><?= esc(lang('Admin.cms_metrics_actions_heading')) ?></p>
            <div class="cms-repeat-body">
            <?php foreach ($actions as $ai => $action) : ?>
                <?= view('admin/pages/partials/action_row', [
                    'name'           => $pfx . '[actions][' . $ai . ']',
                    'action'         => is_array($action) ? $action : [],
                    'defaultVariant' => 'primary',
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_action')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/action_row', [
                    'name'           => $pfx . '[actions][__RI__]',
                    'action'         => ['variant' => 'primary'],
                    'defaultVariant' => 'primary',
                ]) ?>
            </template>
        </div>
    </div>
</div>
