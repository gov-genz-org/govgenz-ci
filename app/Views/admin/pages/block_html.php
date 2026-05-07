<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$html = (string) ($block['html'] ?? '');
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · HTML libre</span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove">Retirer ce bloc</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="html">
        <label class="form-label small">HTML (réservé aux usages avancés)</label>
        <textarea name="<?= esc($pfx, 'attr') ?>[html]" class="form-control font-monospace small" rows="8"><?= esc($html) ?></textarea>
        <div class="form-text">Ce bloc affiche le HTML tel quel sur le site (comme l’éditeur classique). À utiliser avec précaution.</div>
    </div>
</div>
