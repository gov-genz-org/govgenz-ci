<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$rawRows = $b['rows'] ?? [];
if (! is_array($rawRows)) {
    $rawRows = [];
}

$lineRows = [];
foreach (array_values($rawRows) as $row) {
    if (! is_array($row)) {
        continue;
    }
    $item     = admin_pp_scrub_junk_text(trim((string) ($row['item'] ?? '')));
    $quantity = admin_pp_scrub_junk_text(trim((string) ($row['quantity'] ?? '')));
    $notes    = admin_pp_scrub_junk_text(trim((string) ($row['notes'] ?? '')));
    if ($item === '' && $quantity === '' && $notes === '') {
        continue;
    }
    $lineRows[] = [
        'item'     => $item,
        'quantity' => $quantity,
        'notes'    => $notes,
    ];
}
$lineRows[] = ['item' => '', 'quantity' => '', 'notes' => ''];
?>
<div class="project-block-row card mb-3 border-secondary pp-material-needs-block">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0"><?= esc(lang('Admin.block_type_material')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="material_needs">
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_section_title')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>" placeholder="📦 Ressources matérielles recherchées">
        </div>
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_coord_contact')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[contact]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['contact'] ?? '')) ?>" placeholder="Email ou téléphone">
        </div>
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_table_note')) ?></label>
            <textarea name="<?= esc($pfx, 'attr') ?>[footnote]" class="form-control form-control-sm" rows="2" maxlength="2000"><?= esc((string) ($b['footnote'] ?? '')) ?></textarea>
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="rows">
            <div class="row g-2 mb-1 small fw-semibold text-muted d-none d-md-flex align-items-center">
                <div class="col-md-5"><?= esc(lang('Admin.block_row_item')) ?></div>
                <div class="col-md-2"><?= esc(lang('Admin.block_row_qty')) ?></div>
                <div class="col-md"><?= esc(lang('Admin.block_row_details')) ?></div>
                <div class="col-auto ms-auto" style="width:2.75rem"></div>
            </div>
            <div class="pp-repeat-body">
                <?php foreach ($lineRows as $ri => $row) : ?>
                    <?= view('admin/project_projects/blocks/material_needs_row', [
                        'rp'  => $pfx . '[rows][' . $ri . ']',
                        'row' => $row,
                    ]) ?>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2"><?= esc(lang('Admin.block_add_line')) ?></button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/material_needs_row', [
                    'rp'  => $pfx . '[rows][__RI__]',
                    'row' => ['item' => '', 'quantity' => '', 'notes' => ''],
                ]) ?>
            </template>
        </div>
        <p class="form-text small mb-0 mt-2"><?= esc(lang('Admin.block_material_public')) ?></p>
    </div>
</div>
