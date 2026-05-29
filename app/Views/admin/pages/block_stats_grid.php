<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$stats = $b['stats'] ?? ($b['metrics'] ?? []);
if (! is_array($stats)) {
    $stats = [];
}
$stats = array_values(array_filter($stats, static function ($row): bool {
    if (! is_array($row)) {
        return false;
    }

    return trim((string) ($row['value'] ?? '') . (string) ($row['suffix'] ?? '') . (string) ($row['label'] ?? '')) !== '';
}));
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
            <?= esc(lang('Admin.cms_block_type_stats_grid')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="stats_grid">
        <div class="cms-repeatable mb-3 mt-3" data-cms-repeat-key="stats">
            <p class="small fw-semibold mb-2"><?= esc(lang('Admin.cms_stats_rows_heading')) ?></p>
            <div class="cms-repeat-body">
            <?php foreach ($stats as $si => $row) : ?>
                <?= view('admin/pages/partials/stat_row', [
                    'name' => $pfx . '[stats][' . $si . ']',
                    'row'  => is_array($row) ? $row : [],
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_stat')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/stat_row', [
                    'name' => $pfx . '[stats][__RI__]',
                    'row'  => [],
                ]) ?>
            </template>
        </div>
        <div class="mb-2 mt-3">
            <label class="form-label small"><?= esc(lang('Admin.cms_metrics_footnote')) ?></label>
            <textarea name="<?= esc($pfx, 'attr') ?>[footnote]" class="form-control form-control-sm" rows="2"><?= esc((string) ($b['footnote'] ?? '')) ?></textarea>
        </div>
        <div class="cms-repeatable" data-cms-repeat-key="actions">
            <p class="small fw-semibold mb-2 mt-3"><?= esc(lang('Admin.cms_metrics_actions_heading')) ?></p>
            <div class="cms-repeat-body">
            <?php foreach ($actions as $ai => $action) : ?>
                <?= view('admin/pages/partials/action_row', [
                    'name'           => $pfx . '[actions][' . $ai . ']',
                    'action'         => is_array($action) ? $action : [],
                    'defaultVariant' => 'secondary',
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_action')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/action_row', [
                    'name'           => $pfx . '[actions][__RI__]',
                    'action'         => ['variant' => 'secondary'],
                    'defaultVariant' => 'secondary',
                ]) ?>
            </template>
        </div>
    </div>
</div>
