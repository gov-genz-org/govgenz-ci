<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$html = (string) ($block['html'] ?? '');
?>
<div class="cms-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0"><?= esc(lang('Admin.block_type_html')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger cms-block-remove"><?= esc(lang('Admin.cms_block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="html">
        <label class="form-label small"><?= esc(lang('Admin.cms_block_html_page')) ?></label>
        <textarea name="<?= esc($pfx, 'attr') ?>[html]" class="form-control font-monospace small" rows="8"><?= esc($html) ?></textarea>
        <div class="form-text"><?= esc(lang('Admin.cms_block_html_help')) ?></div>
    </div>
</div>
