<?php

declare(strict_types=1);

/** @var string $rp */
/** @var array<string, string> $m */
$m = $m ?? ['name' => '', 'role' => ''];
?>
<div class="pp-repeat-row row g-2 align-items-md-center mb-2">
    <div class="col-12 col-md">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_name_role')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[name]" class="form-control form-control-sm" value="<?= esc((string) ($m['name'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_name_role'), 'attr') ?>">
    </div>
    <div class="col-12 col-md">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_subtitle')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[role]" class="form-control form-control-sm" value="<?= esc((string) ($m['role'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_subtitle_small'), 'attr') ?>">
    </div>
    <div class="col-auto d-flex align-items-center justify-content-end ms-md-auto">
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_member')]) ?>
    </div>
</div>
