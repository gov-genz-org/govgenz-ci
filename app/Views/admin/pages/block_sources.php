<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$lines = $b['lines'] ?? [];
if (! is_array($lines)) {
    $lines = [];
}
$lines = array_values(array_filter($lines, static fn ($line): bool => trim((string) $line) !== ''));
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_sources')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="sources">
        <div class="cms-repeatable" data-cms-repeat-key="lines">
            <div class="cms-repeat-body">
            <?php foreach ($lines as $li => $line) : ?>
                <?= view('admin/pages/partials/source_line_row', [
                    'name'  => $pfx . '[lines][' . $li . ']',
                    'value' => is_string($line) ? $line : '',
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_source_line')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/source_line_row', [
                    'name'  => $pfx . '[lines][__RI__]',
                    'value' => '',
                ]) ?>
            </template>
        </div>
    </div>
</div>
