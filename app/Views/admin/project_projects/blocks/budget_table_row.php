<?php

declare(strict_types=1);

/** @var string $rp */
/** @var array<string, string> $row */
$row = $row ?? ['poste' => '', 'detail' => '', 'montant' => ''];
?>
<div class="pp-repeat-row row g-2 align-items-md-center mb-2">
    <div class="col-12 col-md">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_post')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[poste]" class="form-control form-control-sm" value="<?= esc((string) ($row['poste'] ?? '')) ?>" placeholder="Poste" maxlength="255">
    </div>
    <div class="col-12 col-md">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_details')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[detail]" class="form-control form-control-sm" value="<?= esc((string) ($row['detail'] ?? '')) ?>" placeholder="Détail" maxlength="500">
    </div>
    <div class="col-12 col-md-3">
        <label class="form-label small mb-0 d-md-none"><?= esc(lang('Admin.block_row_amount')) ?></label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[montant]" class="form-control form-control-sm pp-budget-line-montant text-md-end" value="<?= esc((string) ($row['montant'] ?? '')) ?>" placeholder="Montant (Ar)" maxlength="64">
    </div>
    <div class="col-auto d-flex align-items-center justify-content-end ms-md-auto">
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?>
    </div>
</div>
