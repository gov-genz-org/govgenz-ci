<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$bullets = admin_pp_repeat_scalar_lines(is_array($b['bullets'] ?? null) ? $b['bullets'] : []);
$extras  = admin_pp_repeat_scalar_lines(is_array($b['extra_paragraphs'] ?? null) ? $b['extra_paragraphs'] : []);
$style   = strtolower(trim((string) ($b['heading_style'] ?? 'default')));
if (! in_array($style, ['default', 'warm', 'teal'], true)) {
    $style = 'default';
}
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Section (problème / solution / texte)</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="section_rich">
        <div class="row g-2 mb-2">
            <div class="col-md-8">
                <label class="form-label small">Titre de section</label>
                <input type="text" name="<?= esc($pfx, 'attr') ?>[heading]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['heading'] ?? '')) ?>" placeholder="ex. 📰 Le problème">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Style du titre</label>
                <select name="<?= esc($pfx, 'attr') ?>[heading_style]" class="form-select form-select-sm">
                    <option value="default" <?= $style === 'default' ? 'selected' : '' ?>>Neutre</option>
                    <option value="warm" <?= $style === 'warm' ? 'selected' : '' ?>>Warm (problème)</option>
                    <option value="teal" <?= $style === 'teal' ? 'selected' : '' ?>>Teal (solution / impact)</option>
                </select>
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label small">Introduction (texte simple)</label>
            <textarea name="<?= esc($pfx, 'attr') ?>[intro]" class="form-control form-control-sm" rows="3" maxlength="8000"><?= esc((string) ($b['intro'] ?? '')) ?></textarea>
        </div>
        <div class="pp-repeatable mb-4" data-pp-repeat-key="bullets">
            <p class="small fw-semibold mb-2">Puces (lignes vides ignorées à l’enregistrement)</p>
            <div class="pp-repeat-body d-flex flex-column gap-2">
            <?php foreach ($bullets as $bi => $line) : ?>
                <?= view('admin/project_projects/blocks/section_rich_bullet_row', [
                    'name'  => $pfx . '[bullets][' . $bi . ']',
                    'value' => is_string($line) ? $line : '',
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2">+ Puce</button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/section_rich_bullet_row', [
                    'name'  => $pfx . '[bullets][__RI__]',
                    'value' => '',
                ]) ?>
            </template>
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="extra_paragraphs">
            <p class="small fw-semibold mb-2">Paragraphes complémentaires (optionnels)</p>
            <div class="pp-repeat-body d-flex flex-column gap-3">
            <?php foreach ($extras as $ei => $para) : ?>
                <?= view('admin/project_projects/blocks/section_rich_extra_row', [
                    'name'  => $pfx . '[extra_paragraphs][' . $ei . ']',
                    'value' => is_string($para) ? $para : '',
                    'n'     => $ei + 1,
                ]) ?>
            <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2">+ Paragraphe</button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/section_rich_extra_row', [
                    'name'  => $pfx . '[extra_paragraphs][__RI__]',
                    'value' => '',
                    'n'     => 0,
                ]) ?>
            </template>
        </div>
    </div>
</div>
