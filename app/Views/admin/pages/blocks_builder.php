<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $blocksForForm */
?>

<div id="cms-blocks-panel" class="<?= $contentMode === 'blocks' ? '' : 'd-none' ?>">
    <label class="form-label">Blocs de page</label>
    <p class="text-muted small">Ajoutez une ou plusieurs sections sans classes CSS : les champs parlants suffisent. Pour une section « chiffres » comme sur l’étude jeunesse, utilisez <strong>Section avec indicateurs</strong>.</p>

    <div id="cms-blocks-container" class="mb-2">
        <?php foreach ($blocksForForm as $idx => $block) : ?>
            <?php
            $bt = (string) ($block['type'] ?? 'metrics_section');
            if ($bt === 'html') {
                echo view('admin/pages/block_html', ['i' => $idx, 'block' => $block]);
            } else {
                echo view('admin/pages/block_metrics', ['i' => $idx, 'block' => $block]);
            }
            ?>
        <?php endforeach; ?>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-primary" id="cms-add-metrics">+ Section avec indicateurs</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="cms-add-html">+ HTML libre</button>
    </div>
</div>

<div id="cms-proto-metrics" class="d-none" aria-hidden="true">
    <?= view('admin/pages/block_metrics', ['i' => -1, 'block' => ['type' => 'metrics_section']]) ?>
</div>
<div id="cms-proto-html" class="d-none" aria-hidden="true">
    <?= view('admin/pages/block_html', ['i' => -1, 'block' => ['type' => 'html']]) ?>
</div>
