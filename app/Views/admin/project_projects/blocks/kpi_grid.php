<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$items = $b['items'] ?? [];
if (! is_array($items)) {
    $items = [];
}
$items = array_values($items);
while (count($items) < 8) {
    $items[] = ['value' => '', 'label' => ''];
}
$items = array_slice($items, 0, 8);
$style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
if (! in_array($style, ['default', 'warm', 'teal'], true)) {
    $style = 'teal';
}
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Grille KPI</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="kpi_grid">
        <div class="row g-2 mb-2">
            <div class="col-md-8">
                <label class="form-label small">Titre de section</label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Style titre</label>
                <select name="<?= esc($pfx, 'attr') ?>[heading_style]" class="form-select form-select-sm">
                    <option value="default" <?= $style === 'default' ? 'selected' : '' ?>>Neutre</option>
                    <option value="warm" <?= $style === 'warm' ? 'selected' : '' ?>>Warm</option>
                    <option value="teal" <?= $style === 'teal' ? 'selected' : '' ?>>Teal</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Chiffre</th><th>Libellé</th></tr></thead>
                <tbody>
                <?php foreach ($items as $ii => $it) : ?>
                    <?php
                    $it = is_array($it) ? $it : [];
                    $ip = $pfx . '[items][' . $ii . ']';
                    ?>
                    <tr>
                        <td><input type="text" name="<?= esc($ip, 'attr') ?>[value]" class="form-control form-control-sm" value="<?= esc((string) ($it['value'] ?? '')) ?>"></td>
                        <td><input type="text" name="<?= esc($ip, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($it['label'] ?? '')) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
