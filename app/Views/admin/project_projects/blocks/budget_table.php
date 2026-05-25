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
    if (\App\Libraries\ProjectBudgetTableSync::rowIsTotal($row)) {
        continue;
    }
    $poste   = admin_pp_scrub_junk_text(trim((string) ($row['poste'] ?? '')));
    $detail  = admin_pp_scrub_junk_text(trim((string) ($row['detail'] ?? '')));
    $montant = admin_pp_scrub_junk_text(trim((string) ($row['montant'] ?? '')));
    if ($poste === '' && $detail === '' && $montant === '') {
        continue;
    }
    $lineRows[] = [
        'poste'   => $poste,
        'detail'  => $detail,
        'montant' => $montant,
    ];
}
$lineRows[] = ['poste' => '', 'detail' => '', 'montant' => ''];
?>
<div class="project-block-row card mb-3 border-secondary pp-budget-table-block">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
            <button type="button" class="btn btn-sm btn-outline-secondary project-block-drag-handle py-0 px-1" draggable="true" title="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>" aria-label="<?= esc(lang('Admin.block_drag_reorder'), 'attr') ?>">↕</button>
            <?= esc(lang('Admin.block_type_budget')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="budget_table">
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_section_title')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_block_budget_title'), 'attr') ?>">
        </div>
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_table_note')) ?></label>
            <textarea name="<?= esc($pfx, 'attr') ?>[footnote]" class="form-control form-control-sm" rows="2" maxlength="2000"><?= esc((string) ($b['footnote'] ?? '')) ?></textarea>
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="rows">
            <div class="row g-2 mb-1 small fw-semibold text-muted d-none d-md-flex align-items-center">
                <div class="col-md"><?= esc(lang('Admin.block_row_post')) ?></div>
                <div class="col-md"><?= esc(lang('Admin.block_row_details')) ?></div>
                <div class="col-md-3 text-md-end"><?= esc(lang('Admin.block_row_amount')) ?></div>
                <div class="col-auto ms-auto" style="width:2.75rem"></div>
            </div>
            <div class="pp-repeat-body">
                    <?php foreach ($lineRows as $ri => $row) : ?>
                        <?= view('admin/project_projects/blocks/budget_table_row', [
                            'rp'  => $pfx . '[rows][' . $ri . ']',
                            'row' => $row,
                        ]) ?>
                    <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2"><?= esc(lang('Admin.block_add_line')) ?></button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/budget_table_row', [
                    'rp'  => $pfx . '[rows][__RI__]',
                    'row' => ['poste' => '', 'detail' => '', 'montant' => ''],
                ]) ?>
            </template>
        </div>
        <p class="form-text small mb-0 mt-2"><?= lang('Admin.block_total_auto') ?></p>
    </div>
</div>
