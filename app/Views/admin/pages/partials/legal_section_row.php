<?php

declare(strict_types=1);

/** @var string $name */
/** @var array<string, mixed> $section */

$section = is_array($section ?? null) ? $section : [];
$bulletsText = (string) ($section['bullets_text'] ?? (is_array($section['bullets'] ?? null) ? implode("\n", $section['bullets']) : ''));
?>
<div class="cms-repeat-row border rounded p-2 bg-light-subtle">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
        <span class="small fw-semibold"><?= esc(lang('Admin.cms_legal_heading')) ?></span>
        <?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?>
    </div>
    <input type="text" name="<?= esc($name, 'attr') ?>[heading]" class="form-control form-control-sm mb-2" value="<?= esc((string) ($section['heading'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_legal_heading'), 'attr') ?>">
    <textarea name="<?= esc($name, 'attr') ?>[body]" class="form-control form-control-sm mb-2" rows="3" placeholder="<?= esc(lang('Admin.cms_legal_body'), 'attr') ?>"><?= esc((string) ($section['body'] ?? '')) ?></textarea>
    <textarea name="<?= esc($name, 'attr') ?>[bullets_text]" class="form-control form-control-sm" rows="2" placeholder="<?= esc(lang('Admin.cms_card_bullets'), 'attr') ?>"><?= esc($bulletsText) ?></textarea>
</div>
