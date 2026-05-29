<?php

declare(strict_types=1);

/** @var string $name */
/** @var array<string, mixed> $action */
/** @var string $defaultVariant */

$action = is_array($action ?? null) ? $action : [];
$defaultVariant = (string) ($defaultVariant ?? 'primary');
$variant = strtolower(trim((string) ($action['variant'] ?? $defaultVariant)));
$variant = $variant === 'secondary' ? 'secondary' : 'primary';
?>
<div class="cms-repeat-row row g-2 align-items-center mb-2">
    <div class="col-md-4"><input type="text" name="<?= esc($name, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($action['label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_label'), 'attr') ?>"></div>
    <div class="col-md-5"><input type="text" name="<?= esc($name, 'attr') ?>[href]" class="form-control form-control-sm" value="<?= esc((string) ($action['href'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_cms_action_href'), 'attr') ?>"></div>
    <div class="col-md-2">
        <select name="<?= esc($name, 'attr') ?>[variant]" class="form-select form-select-sm">
            <option value="primary" <?= $variant === 'primary' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_primary')) ?></option>
            <option value="secondary" <?= $variant === 'secondary' ? 'selected' : '' ?>><?= esc(lang('Admin.cms_variant_secondary')) ?></option>
        </select>
    </div>
    <div class="col-md-1 d-flex justify-content-md-end"><?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?></div>
</div>
