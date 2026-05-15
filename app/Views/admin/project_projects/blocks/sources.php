<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$lines = admin_pp_repeat_scalar_lines(is_array($b['lines'] ?? null) ? $b['lines'] : []);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Sources & documents (liste)</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="sources">
        <div class="mb-2">
            <label class="form-label small">Titre de section</label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="lines">
            <p class="small text-muted mb-1">Une ligne = une puce (texte simple ; pas de HTML).</p>
            <div class="pp-repeat-body">
            <?php foreach ($lines as $li => $line) : ?>
                <?= view('admin/project_projects/blocks/sources_line_row', [
                    'name'  => $pfx . '[lines][' . $li . ']',
                    'value' => is_string($line) ? $line : '',
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2">+ Ligne</button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/sources_line_row', [
                    'name'  => $pfx . '[lines][__RI__]',
                    'value' => '',
                ]) ?>
            </template>
        </div>
    </div>
</div>
