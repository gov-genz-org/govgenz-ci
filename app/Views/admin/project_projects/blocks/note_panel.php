<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
if (! in_array($style, ['default', 'warm', 'teal'], true)) {
    $style = 'teal';
}
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Encadré (suivi, message)</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="note_panel">
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
        <div class="mb-2">
            <label class="form-label small">Message principal</label>
            <textarea name="<?= esc($pfx, 'attr') ?>[message]" class="form-control form-control-sm" rows="3" maxlength="2000"><?= esc((string) ($b['message'] ?? '')) ?></textarea>
        </div>
        <div class="mb-0">
            <label class="form-label small">Sous-texte (optionnel)</label>
            <textarea name="<?= esc($pfx, 'attr') ?>[submessage]" class="form-control form-control-sm" rows="2" maxlength="1000"><?= esc((string) ($b['submessage'] ?? '')) ?></textarea>
        </div>
    </div>
</div>
