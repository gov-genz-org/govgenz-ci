<?php

declare(strict_types=1);

/** @var string $name */
/** @var array<string, mixed> $link */

$link = is_array($link ?? null) ? $link : [];
$soon = (int) ($link['soon'] ?? 0) === 1;
?>
<div class="cms-repeat-row row g-2 align-items-center mb-2">
    <div class="col-md-4">
        <input type="text" name="<?= esc($name, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($link['label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_footer_link_label'), 'attr') ?>">
    </div>
    <div class="col-md-5">
        <input type="text" name="<?= esc($name, 'attr') ?>[href]" class="form-control form-control-sm" value="<?= esc((string) ($link['href'] ?? '')) ?>" placeholder="/contact ou mailto:..." <?= $soon ? 'disabled' : '' ?>>
    </div>
    <div class="col-md-2">
        <div class="form-check form-switch mb-0">
            <input type="hidden" name="<?= esc($name, 'attr') ?>[soon]" value="0">
            <input type="checkbox" class="form-check-input js-footer-link-soon" name="<?= esc($name, 'attr') ?>[soon]" value="1" id="<?= esc($name, 'attr') ?>-soon" <?= $soon ? 'checked' : '' ?>>
            <label class="form-check-label small" for="<?= esc($name, 'attr') ?>-soon"><?= esc(lang('Admin.cms_footer_link_soon')) ?></label>
        </div>
    </div>
    <div class="col-md-1 d-flex justify-content-md-end"><?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?></div>
</div>
