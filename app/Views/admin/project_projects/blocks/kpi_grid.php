<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$rawItems = is_array($b['items'] ?? null) ? $b['items'] : [];
$scrubbedItems = [];
foreach (array_values($rawItems) as $it) {
    if (! is_array($it)) {
        continue;
    }
    $scrubbedItems[] = [
        'value' => admin_pp_scrub_junk_text(trim((string) ($it['value'] ?? ''))),
        'label' => admin_pp_scrub_junk_text(trim((string) ($it['label'] ?? ''))),
    ];
}
$items = admin_pp_repeat_object_rows(
    $scrubbedItems,
    static fn (array $it): bool => trim((string) ($it['value'] ?? '')) === '' && trim((string) ($it['label'] ?? '')) === '',
    ['value' => '', 'label' => ''],
);
$style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
if (! in_array($style, ['default', 'warm', 'teal'], true)) {
    $style = 'teal';
}
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0"><?= esc(lang('Admin.block_type_kpi')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="kpi_grid">
        <div class="row g-2 mb-2">
            <div class="col-md-8">
                <label class="form-label small"><?= esc(lang('Admin.block_section_title')) ?></label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small"><?= esc(lang('Admin.block_style_title_short')) ?></label>
                <select name="<?= esc($pfx, 'attr') ?>[heading_style]" class="form-select form-select-sm">
                    <option value="default" <?= $style === 'default' ? 'selected' : '' ?>><?= esc(lang('Admin.block_style_default')) ?></option>
                    <option value="warm" <?= $style === 'warm' ? 'selected' : '' ?>><?= esc(lang('Admin.block_style_warm')) ?></option>
                    <option value="teal" <?= $style === 'teal' ? 'selected' : '' ?>><?= esc(lang('Admin.block_style_teal')) ?></option>
                </select>
            </div>
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="items">
            <div class="row g-2 mb-1 small fw-semibold text-muted d-none d-md-flex align-items-center">
                <div class="col-md-4"><?= esc(lang('Admin.block_row_figure')) ?></div>
                <div class="col-md"><?= esc(lang('Admin.block_row_label')) ?></div>
                <div class="col-auto ms-auto" style="width:2.75rem"></div>
            </div>
            <div class="pp-repeat-body">
                    <?php foreach ($items as $ii => $it) : ?>
                        <?= view('admin/project_projects/blocks/kpi_grid_row', [
                            'rp' => $pfx . '[items][' . $ii . ']',
                            'it' => is_array($it) ? $it : [],
                        ]) ?>
                    <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2"><?= esc(lang('Admin.block_add_kpi')) ?></button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/kpi_grid_row', [
                    'rp' => $pfx . '[items][__RI__]',
                    'it' => ['value' => '', 'label' => ''],
                ]) ?>
            </template>
        </div>
    </div>
</div>
