<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$rows = admin_pp_repeat_object_rows(
    is_array($b['rows'] ?? null) ? $b['rows'] : [],
    static fn (array $row): bool => trim((string) ($row['label'] ?? '')) === ''
        && trim((string) ($row['numbers'] ?? '')) === ''
        && (int) ($row['bar_percent'] ?? 0) === 0,
    ['label' => '', 'numbers' => '', 'bar_percent' => 0],
);
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
        <div class="pp-repeatable" data-pp-repeat-key="rows">
            <div class="row g-2 mb-1 small fw-semibold text-muted d-none d-md-flex align-items-center">
                <div class="col-md-4">Libellé</div>
                <div class="col-md">Chiffres / texte</div>
                <div class="col-md-2">Barre %</div>
                <div class="col-auto ms-auto" style="width:2.75rem"></div>
            </div>
            <div class="pp-repeat-body">
                    <?php foreach ($rows as $ri => $row) : ?>
                        <?= view('admin/project_projects/blocks/impact_tracker_row', [
                            'rp'  => $pfx . '[rows][' . $ri . ']',
                            'row' => is_array($row) ? $row : [],
                        ]) ?>
                    <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2">+ Ligne</button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/impact_tracker_row', [
                    'rp'  => $pfx . '[rows][__RI__]',
                    'row' => ['label' => '', 'numbers' => '', 'bar_percent' => 0],
                ]) ?>
            </template>
        </div>
    </div>
</div>
