<?php

declare(strict_types=1);

/** @var string $pfx Prefixe name="blocks[i]…" */
/** @var array<string, mixed> $block */
/** @var string|null $kickerPlaceholder */
/** @var string|null $headingIdPlaceholder */

$b = $block;
$kickerPlaceholder = $kickerPlaceholder ?? lang('Admin.cms_sectors_kicker_placeholder');
$headingIdPlaceholder = $headingIdPlaceholder ?? 'secteurs-heading';
?>
<fieldset class="border rounded p-3 mb-3">
    <legend class="float-none w-auto px-2 small fw-semibold mb-0"><?= esc(lang('Admin.cms_block_section_intro')) ?></legend>
    <p class="form-text small mb-2"><?= esc(lang('Admin.cms_block_section_intro_help')) ?></p>
    <div class="mb-2">
        <label class="form-label small mb-1"><?= esc(lang('Admin.cms_block_kicker')) ?></label>
        <input type="text" name="<?= esc($pfx, 'attr') ?>[kicker]" class="form-control form-control-sm" value="<?= esc((string) ($b['kicker'] ?? '')) ?>" placeholder="<?= esc($kickerPlaceholder, 'attr') ?>">
    </div>
    <div class="mb-2">
        <label class="form-label small mb-1"><?= esc(lang('Admin.cms_block_title')) ?></label>
        <input type="text" name="<?= esc($pfx, 'attr') ?>[title]" class="form-control form-control-sm" value="<?= esc((string) ($b['title'] ?? '')) ?>">
    </div>
    <div class="mb-2">
        <label class="form-label small mb-1"><?= esc(lang('Admin.cms_block_lead')) ?></label>
        <textarea name="<?= esc($pfx, 'attr') ?>[lead]" class="form-control form-control-sm" rows="2"><?= esc((string) ($b['lead'] ?? '')) ?></textarea>
    </div>
    <div>
        <label class="form-label small mb-1"><?= esc(lang('Admin.cms_block_heading_id')) ?></label>
        <input type="text" name="<?= esc($pfx, 'attr') ?>[heading_id]" class="form-control form-control-sm" value="<?= esc((string) ($b['heading_id'] ?? '')) ?>" placeholder="<?= esc($headingIdPlaceholder, 'attr') ?>">
    </div>
</fieldset>
