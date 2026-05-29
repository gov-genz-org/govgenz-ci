<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$paragraphs = $b['paragraphs'] ?? [];
if (! is_array($paragraphs)) {
    $paragraphs = [];
}
$paragraphs = array_values(array_filter($paragraphs, static fn ($value): bool => trim((string) $value) !== ''));

$bullets = $b['bullets'] ?? [];
if (! is_array($bullets)) {
    $bullets = [];
}
$bullets = array_values(array_filter($bullets, static fn ($value): bool => trim((string) $value) !== ''));
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_section_text')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="section_text">
        <div class="cms-repeatable mb-3 mt-3" data-cms-repeat-key="paragraphs">
            <p class="small fw-semibold mb-2"><?= esc(lang('Admin.cms_section_paragraphs')) ?></p>
            <div class="cms-repeat-body d-flex flex-column gap-2">
            <?php foreach ($paragraphs as $pi => $paragraph) : ?>
                <?= view('admin/pages/partials/section_text_paragraph_row', [
                    'name'  => $pfx . '[paragraphs][' . $pi . ']',
                    'value' => is_string($paragraph) ? $paragraph : '',
                    'n'     => $pi + 1,
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.block_add_paragraph')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/section_text_paragraph_row', [
                    'name'  => $pfx . '[paragraphs][__RI__]',
                    'value' => '',
                    'n'     => 0,
                ]) ?>
            </template>
        </div>
        <div class="cms-repeatable mb-3" data-cms-repeat-key="bullets">
            <p class="small fw-semibold mb-2"><?= esc(lang('Admin.cms_section_bullets')) ?></p>
            <div class="cms-repeat-body d-flex flex-column gap-2">
            <?php foreach ($bullets as $bi => $bullet) : ?>
                <?= view('admin/pages/partials/section_text_bullet_row', [
                    'name'  => $pfx . '[bullets][' . $bi . ']',
                    'value' => is_string($bullet) ? $bullet : '',
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.block_add_bullet')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/section_text_bullet_row', [
                    'name'  => $pfx . '[bullets][__RI__]',
                    'value' => '',
                ]) ?>
            </template>
        </div>
        <div class="mb-0 mt-3">
            <label class="form-label small"><?= esc(lang('Admin.cms_field_source')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[source]" class="form-control form-control-sm" value="<?= esc((string) ($b['source'] ?? '')) ?>">
        </div>
    </div>
</div>
