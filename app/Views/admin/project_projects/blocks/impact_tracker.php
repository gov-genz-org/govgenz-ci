<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$rows = $b['rows'] ?? [];
if (! is_array($rows)) {
    $rows = [];
}
$rows = array_values($rows);
while (count($rows) < 5) {
    $rows[] = ['label' => '', 'numbers' => '', 'bar_percent' => 0];
}
$rows = array_slice($rows, 0, 8);
$style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
if (! in_array($style, ['default', 'warm', 'teal'], true)) {
    $style = 'teal';
}
$defaultTitle = "🎯 Suivi d'impact — Résultats actuels";
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Suivi d'impact</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="impact_tracker">
        <div class="row g-2 mb-2">
            <div class="col-md-8">
                <label class="form-label small">Titre de section</label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? $defaultTitle)) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Style titre</label>
                <select name="<?= esc($pfx, 'attr') ?>[heading_style]" class="form-select form-select-sm">
                    <option value="default" <?= $style === 'default' ? 'selected' : '' ?>>Neutre</option>
                    <option value="warm" <?= $style === 'warm' ? 'selected' : '' ?>>Warm</option>
                    <option value="teal" <?= $style === 'teal' ? 'selected' : '' ?>>Teal</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small">Note (sous le titre)</label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[note]" class="form-control form-control-sm" maxlength="500" value="<?= esc((string) ($b['note'] ?? '')) ?>" placeholder="Données au …">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Chiffres / texte</th>
                    <th style="width:7rem">Barre %</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $ri => $row) : ?>
                    <?php
                    $row = is_array($row) ? $row : [];
                    $rp = $pfx . '[rows][' . $ri . ']';
                    $pct = (int) ($row['bar_percent'] ?? 0);
                    if ($pct < 0) {
                        $pct = 0;
                    }
                    if ($pct > 100) {
                        $pct = 100;
                    }
                    ?>
                    <tr>
                        <td><input type="text" name="<?= esc($rp, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($row['label'] ?? '')) ?>"></td>
                        <td><input type="text" name="<?= esc($rp, 'attr') ?>[numbers]" class="form-control form-control-sm" value="<?= esc((string) ($row['numbers'] ?? '')) ?>" placeholder="38 % de la phase"></td>
                        <td><input type="number" name="<?= esc($rp, 'attr') ?>[bar_percent]" class="form-control form-control-sm" min="0" max="100" value="<?= esc((string) $pct) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
