<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $blocksForForm */
?>

<div id="cms-blocks-panel" class="<?= $contentMode === 'blocks' ? '' : 'd-none' ?>">
    <label class="form-label"><?= esc(lang('Admin.cms_blocks_label')) ?></label>
    <p class="text-muted small"><?= lang('Admin.cms_blocks_help') ?></p>

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
        <button type="button" class="btn btn-sm btn-outline-primary" id="cms-add-metrics"><?= esc(lang('Admin.cms_add_metrics')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="cms-add-html"><?= esc(lang('Admin.cms_add_html')) ?></button>
    </div>
</div>

<div id="cms-proto-metrics" class="d-none" aria-hidden="true">
    <?= view('admin/pages/block_metrics', ['i' => -1, 'block' => ['type' => 'metrics_section']]) ?>
</div>
<div id="cms-proto-html" class="d-none" aria-hidden="true">
    <?= view('admin/pages/block_html', ['i' => -1, 'block' => ['type' => 'html']]) ?>
</div>
