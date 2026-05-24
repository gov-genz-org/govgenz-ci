<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;

$mets = $b['metrics'] ?? [];
if (! is_array($mets)) {
    $mets = [];
}
$mets = array_values($mets);
while (count($mets) < 8) {
    $mets[] = ['value' => '', 'label' => ''];
}
$mets = array_slice($mets, 0, 8);

$acts = $b['actions'] ?? [];
if (! is_array($acts)) {
    $acts = [];
}
$acts = array_values($acts);
while (count($acts) < 3) {
    $acts[] = ['label' => '', 'href' => '', 'variant' => 'secondary'];
}
$acts = array_slice($acts, 0, 3);
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0"><?= esc(lang('Admin.cms_block_type_metrics')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="metrics_section">
        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="form-label small"><?= esc(lang('Admin.cms_metrics_kicker')) ?></label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[kicker]" class="form-control form-control-sm" value="<?= esc((string) ($b['kicker'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small"><?= esc(lang('Admin.cms_metrics_heading_id')) ?></label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[heading_id]" class="form-control form-control-sm" value="<?= esc((string) ($b['heading_id'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_cms_heading_id'), 'attr') ?>">
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.cms_metrics_title')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[title]" class="form-control form-control-sm" value="<?= esc((string) ($b['title'] ?? '')) ?>">
        </div>
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.cms_metrics_lead')) ?></label>
            <textarea name="<?= esc($pfx, 'attr') ?>[lead]" class="form-control form-control-sm" rows="2"><?= esc((string) ($b['lead'] ?? '')) ?></textarea>
        </div>
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.cms_metrics_footnote')) ?></label>
            <textarea name="<?= esc($pfx, 'attr') ?>[footnote]" class="form-control form-control-sm" rows="2"><?= esc((string) ($b['footnote'] ?? '')) ?></textarea>
        </div>

        <p class="small fw-semibold mb-1 mt-3"><?= esc(lang('Admin.cms_metrics_rows_heading')) ?></p>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th><?= esc(lang('Admin.cms_metrics_col_value')) ?></th><th><?= esc(lang('Admin.block_row_label')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($mets as $mi => $row) : ?>
                    <?php
                    $row = is_array($row) ? $row : [];
                    $mp = $pfx . '[metrics][' . $mi . ']';
                    ?>
                    <tr>
                        <td><input type="text" name="<?= esc($mp, 'attr') ?>[value]" class="form-control form-control-sm" value="<?= esc((string) ($row['value'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_cms_metrics_value'), 'attr') ?>"></td>
                        <td><input type="text" name="<?= esc($mp, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($row['label'] ?? '')) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="small fw-semibold mb-1 mt-3"><?= esc(lang('Admin.cms_metrics_actions_heading')) ?></p>
        <?php foreach ($acts as $ai => $row) : ?>
            <?php
            $row = is_array($row) ? $row : [];
            $ap = $pfx . '[actions][' . $ai . ']';
            $var = strtolower(trim((string) ($row['variant'] ?? 'secondary')));
            $var = $var === 'primary' ? 'primary' : 'secondary';
            ?>
            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <input type="text" name="<?= esc($ap, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($row['label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_label'), 'attr') ?>">
                </div>
                <div class="col-md-5">
                    <input type="text" name="<?= esc($ap, 'attr') ?>[href]" class="form-control form-control-sm" value="<?= esc((string) ($row['href'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_cms_action_href'), 'attr') ?>">
                </div>
                <div class="col-md-2">
                    <select name="<?= esc($ap, 'attr') ?>[variant]" class="form-select form-select-sm">
                        <option value="secondary" <?= $var === 'secondary' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_secondary')) ?></option>
                        <option value="primary" <?= $var === 'primary' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_primary')) ?></option>
                    </select>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
