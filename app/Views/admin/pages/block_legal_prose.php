<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$sections = $b['sections'] ?? [];
if (! is_array($sections)) {
    $sections = [];
}
$sections = array_values(array_filter($sections, static function ($section): bool {
    if (! is_array($section)) {
        return false;
    }

    return trim(implode('', [
        (string) ($section['heading'] ?? ''),
        (string) ($section['body'] ?? ''),
        (string) ($section['bullets_text'] ?? ''),
        is_array($section['bullets'] ?? null) ? implode('', $section['bullets']) : '',
    ])) !== '';
}));
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_legal_prose')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="legal_prose">
        <p class="text-muted small"><?= esc(lang('Admin.cms_legal_help')) ?></p>
        <div class="cms-repeatable" data-cms-repeat-key="sections">
            <div class="cms-repeat-body d-flex flex-column gap-2">
            <?php foreach ($sections as $si => $section) : ?>
                <?= view('admin/pages/partials/legal_section_row', [
                    'name'    => $pfx . '[sections][' . $si . ']',
                    'section' => is_array($section) ? $section : [],
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_legal_section')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/legal_section_row', [
                    'name'    => $pfx . '[sections][__RI__]',
                    'section' => [],
                ]) ?>
            </template>
        </div>
    </div>
</div>
