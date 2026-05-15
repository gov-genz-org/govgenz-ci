<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$phases = $b['phases'] ?? [];
if (! is_array($phases)) {
    $phases = [];
}
$phases = array_values($phases);
while (count($phases) < 5) {
    $phases[] = ['phase_label' => '', 'duration' => '', 'step_title' => '', 'body' => ''];
}
$phases = array_slice($phases, 0, 5);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Calendrier (phases)</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="timeline">
        <div class="mb-2">
            <label class="form-label small">Titre de section</label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>" placeholder="📅 Calendrier">
        </div>
        <?php foreach ($phases as $pi => $ph) : ?>
            <?php $ph = is_array($ph) ? $ph : []; ?>
            <?php $pp = $pfx . '[phases][' . $pi . ']'; ?>
            <div class="border rounded p-2 mb-2 bg-light">
                <p class="small fw-semibold mb-2">Phase <?= $pi + 1 ?></p>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small">Libellé phase</label>
                        <input type="text" name="<?= esc($pp, 'attr') ?>[phase_label]" class="form-control form-control-sm" value="<?= esc((string) ($ph['phase_label'] ?? '')) ?>" placeholder="Phase 1">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small">Durée / période</label>
                        <input type="text" name="<?= esc($pp, 'attr') ?>[duration]" class="form-control form-control-sm" value="<?= esc((string) ($ph['duration'] ?? '')) ?>" placeholder="0 – 8 mois">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Titre de l’étape</label>
                        <input type="text" name="<?= esc($pp, 'attr') ?>[step_title]" class="form-control form-control-sm" value="<?= esc((string) ($ph['step_title'] ?? '')) ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Description</label>
                        <textarea name="<?= esc($pp, 'attr') ?>[body]" class="form-control form-control-sm" rows="2" maxlength="4000"><?= esc((string) ($ph['body'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
