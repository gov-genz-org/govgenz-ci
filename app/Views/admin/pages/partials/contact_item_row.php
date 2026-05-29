<?php

declare(strict_types=1);

/** @var string $name */
/** @var array<string, mixed> $item */

$item = is_array($item ?? null) ? $item : [];
?>
<div class="cms-repeat-row row g-2 align-items-center mb-2">
    <div class="col-md-2"><input type="text" name="<?= esc($name, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($item['label'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_contact_label'), 'attr') ?>"></div>
    <div class="col-md-3"><input type="text" name="<?= esc($name, 'attr') ?>[title]" class="form-control form-control-sm" value="<?= esc((string) ($item['title'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_contact_title'), 'attr') ?>"></div>
    <div class="col-md-3"><input type="text" name="<?= esc($name, 'attr') ?>[subtitle]" class="form-control form-control-sm" value="<?= esc((string) ($item['subtitle'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_subtitle'), 'attr') ?>"></div>
    <div class="col-md-3"><input type="text" name="<?= esc($name, 'attr') ?>[href]" class="form-control form-control-sm" value="<?= esc((string) ($item['href'] ?? '')) ?>" placeholder="mailto:... ou /join"></div>
    <div class="col-md-1 d-flex justify-content-md-end"><?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?></div>
</div>
