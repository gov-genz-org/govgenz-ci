<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$bullets = $b['bullets'] ?? [];
if (! is_array($bullets)) {
    $bullets = [];
}
$bullets = array_values($bullets);
while (count($bullets) < 10) {
    $bullets[] = '';
}
$bullets = array_slice($bullets, 0, 10);
$extras = $b['extra_paragraphs'] ?? [];
if (! is_array($extras)) {
    $extras = [];
}
$extras = array_values($extras);
while (count($extras) < 2) {
    $extras[] = '';
}
$extras = array_slice($extras, 0, 2);
$style = strtolower(trim((string) ($b['heading_style'] ?? 'default')));
if (! in_array($style, ['default', 'warm', 'teal'], true)) {
    $style = 'default';
}
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Section (problème / solution / texte)</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="section_rich">
        <div class="row g-2 mb-2">
            <div class="col-md-8">
                <label class="form-label small">Titre de section</label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[heading]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['heading'] ?? '')) ?>" placeholder="ex. 📰 Le problème">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Style du titre</label>
                <select name="<?= esc($pfx, 'attr') ?>[heading_style]" class="form-select form-select-sm">
                    <option value="default" <?= $style === 'default' ? 'selected' : '' ?>>Neutre</option>
                    <option value="warm" <?= $style === 'warm' ? 'selected' : '' ?>>Warm (problème)</option>
                    <option value="teal" <?= $style === 'teal' ? 'selected' : '' ?>>Teal (solution / impact)</option>
                </select>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label small">Introduction (texte simple)</label>
            <textarea name="<?= esc($pfx, 'attr') ?>[intro]" class="form-control form-control-sm" rows="3" maxlength="8000"><?= esc((string) ($b['intro'] ?? '')) ?></textarea>
        </div>
        <p class="small fw-semibold mb-1">Puces (lignes vides ignorées)</p>
        <div class="table-responsive mb-2">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Texte de la puce</th></tr></thead>
                <tbody>
                <?php foreach ($bullets as $bi => $line) : ?>
                    <tr>
                        <td><input type="text" name="<?= esc($pfx, 'attr') ?>[bullets][<?= (int) $bi ?>]" class="form-control form-control-sm" value="<?= esc(is_string($line) ? $line : '') ?>" maxlength="500"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php foreach ($extras as $ei => $para) : ?>
            <div class="mb-2">
                <label class="form-label small">Paragraphe complémentaire <?= $ei + 1 ?> (optionnel)</label>
                <textarea name="<?= esc($pfx, 'attr') ?>[extra_paragraphs][<?= (int) $ei ?>]" class="form-control form-control-sm" rows="2" maxlength="4000"><?= esc(is_string($para) ? $para : '') ?></textarea>
            </div>
        <?php endforeach; ?>
    </div>
</div>
