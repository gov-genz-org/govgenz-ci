<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$columns = $b['columns'] ?? [];
if (! is_array($columns)) {
    $columns = [];
}
$columns = array_values(array_filter($columns, static function ($column): bool {
    if (! is_array($column)) {
        return false;
    }
    $title = trim((string) ($column['title'] ?? ''));
    $links = $column['links'] ?? [];
    if (! is_array($links)) {
        return false;
    }
    foreach ($links as $link) {
        if (is_array($link) && trim((string) ($link['label'] ?? '')) !== '') {
            return true;
        }
    }

    return $title !== '';
}));
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_footer_columns')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="footer_columns">
        <p class="text-muted small"><?= esc(lang('Admin.cms_footer_columns_help')) ?></p>
        <div class="cms-repeatable" data-cms-repeat-key="columns">
            <div class="cms-repeat-body d-flex flex-column gap-2">
                <?php foreach ($columns as $ci => $column) : ?>
                    <?= view('admin/pages/partials/footer_column_row', [
                        'name'   => $pfx . '[columns][' . $ci . ']',
                        'column' => is_array($column) ? $column : [],
                    ]) ?>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_footer_column')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/footer_column_row', [
                    'name'   => $pfx . '[columns][__RI__]',
                    'column' => [],
                ]) ?>
            </template>
        </div>
    </div>
</div>
