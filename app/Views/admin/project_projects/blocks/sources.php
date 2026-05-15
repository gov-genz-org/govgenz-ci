<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$lines = $b['lines'] ?? [];
if (! is_array($lines)) {
    $lines = [];
}
$lines = array_values($lines);
while (count($lines) < 12) {
    $lines[] = '';
}
$lines = array_slice($lines, 0, 12);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Sources & documents (liste)</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="sources">
        <div class="mb-2">
            <label class="form-label small">Titre de section</label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
        </div>
        <p class="small text-muted mb-1">Une ligne = une puce (texte simple ; pas de HTML).</p>
        <?php foreach ($lines as $li => $line) : ?>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[lines][<?= (int) $li ?>]" class="form-control form-control-sm mb-1" value="<?= esc(is_string($line) ? $line : '') ?>" maxlength="500">
        <?php endforeach; ?>
    </div>
</div>
