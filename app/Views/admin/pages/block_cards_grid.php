<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$variant = strtolower(trim((string) ($b['variant'] ?? 'simple_cards')));
if (! in_array($variant, ['simple_cards', 'circle_cards', 'pillar_cards', 'tile_grid'], true)) {
    $variant = 'simple_cards';
}
$cards = $b['cards'] ?? [];
if (! is_array($cards)) {
    $cards = [];
}
$cards = array_values(array_filter($cards, static function ($card): bool {
    if (! is_array($card)) {
        return false;
    }

    return trim(implode('', [
        (string) ($card['eyebrow'] ?? ''),
        (string) ($card['value'] ?? ''),
        (string) ($card['unit'] ?? ''),
        (string) ($card['title'] ?? ''),
        (string) ($card['subtitle'] ?? ''),
        (string) ($card['description'] ?? ''),
        (string) ($card['href'] ?? ''),
        (string) ($card['media_id'] ?? ''),
        (string) ($card['media_alt'] ?? ''),
        (string) ($card['media_id_2'] ?? ''),
        (string) ($card['media_alt_2'] ?? ''),
        (string) ($card['icon_url'] ?? ''),
        (string) ($card['bullets_text'] ?? ''),
        is_array($card['bullets'] ?? null) ? implode('', $card['bullets']) : '',
    ])) !== '';
}));
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary cms-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.cms_block_type_cards_grid')) ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="cards_grid">
        <div class="mb-3">
            <label class="form-label small"><?= esc(lang('Admin.cms_cards_variant')) ?></label>
            <select name="<?= esc($pfx, 'attr') ?>[variant]" class="form-select form-select-sm" style="max-width:24rem">
                <option value="simple_cards" <?= $variant === 'simple_cards' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_simple_cards')) ?></option>
                <option value="circle_cards" <?= $variant === 'circle_cards' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_circle_cards')) ?></option>
                <option value="pillar_cards" <?= $variant === 'pillar_cards' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_pillar_cards')) ?></option>
                <option value="tile_grid" <?= $variant === 'tile_grid' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_tile_grid')) ?></option>
            </select>
        </div>

        <div class="cms-repeatable mb-3" data-cms-repeat-key="cards">
            <p class="small fw-semibold mb-2"><?= esc(lang('Admin.cms_cards_rows_heading')) ?></p>
            <div class="cms-repeat-body d-flex flex-column gap-2">
            <?php foreach ($cards as $ci => $card) : ?>
                <?= view('admin/pages/partials/card_row', [
                    'name' => $pfx . '[cards][' . $ci . ']',
                    'card' => is_array($card) ? $card : [],
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_card')) ?></button>
            <template class="cms-repeat-template">
                <?= view('admin/pages/partials/card_row', [
                    'name' => $pfx . '[cards][__RI__]',
                    'card' => [],
                ]) ?>
            </template>
        </div>
        <div class="mb-0">
            <label class="form-label small"><?= esc(lang('Admin.cms_field_source')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[source]" class="form-control form-control-sm" value="<?= esc((string) ($b['source'] ?? '')) ?>">
        </div>
    </div>
</div>
