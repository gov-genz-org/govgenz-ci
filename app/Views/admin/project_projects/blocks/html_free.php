<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$html = (string) ($block['html'] ?? '');
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · HTML libre</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="html">
        <label class="form-label small">HTML (avancé)</label>
        <textarea name="<?= esc($pfx, 'attr') ?>[html]" class="form-control font-monospace small" rows="6"><?= esc($html) ?></textarea>
    </div>
</div>
