<?php

declare(strict_types=1);

/** @var string $rp */
/** @var array<string, string> $it */
$it = $it ?? ['value' => '', 'label' => ''];
?>
<div class="pp-repeat-row row g-2 align-items-md-center mb-2">
    <div class="col-12 col-md-4">
        <label class="form-label small mb-0 d-md-none">Chiffre</label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[value]" class="form-control form-control-sm" value="<?= esc((string) ($it['value'] ?? '')) ?>" placeholder="Chiffre">
    </div>
    <div class="col-12 col-md">
        <label class="form-label small mb-0 d-md-none">Libellé</label>
        <input type="text" name="<?= esc($rp, 'attr') ?>[label]" class="form-control form-control-sm" value="<?= esc((string) ($it['label'] ?? '')) ?>" placeholder="Libellé">
    </div>
    <div class="col-auto d-flex align-items-center justify-content-end ms-md-auto">
        <?= view('admin/project_projects/blocks/partials/repeat_remove_button', ['title' => 'Retirer ce KPI']) ?>
    </div>
</div>
