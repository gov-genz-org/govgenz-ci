<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$rows = $b['rows'] ?? [];
if (! is_array($rows)) {
    $rows = [];
}
$rows = array_values($rows);
while (count($rows) < 8) {
    $rows[] = ['poste' => '', 'detail' => '', 'montant' => '', 'is_total' => ''];
}
$rows = array_slice($rows, 0, 8);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Tableau budget</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="budget_table">
        <div class="mb-2">
            <label class="form-label small">Titre de section</label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>" placeholder="💰 Budget détaillé">
        </div>
        <div class="mb-2">
            <label class="form-label small">Note sous le tableau</label>
            <textarea name="<?= esc($pfx, 'attr') ?>[footnote]" class="form-control form-control-sm" rows="2" maxlength="2000"><?= esc((string) ($b['footnote'] ?? '')) ?></textarea>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Poste</th><th>Détail</th><th>Montant (Ar)</th><th class="text-center">Total</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $ri => $row) : ?>
                    <?php
                    $row = is_array($row) ? $row : [];
                    $rp = $pfx . '[rows][' . $ri . ']';
                    $it = strtolower(trim((string) ($row['is_total'] ?? ''))) === '1' || strtolower(trim((string) ($row['is_total'] ?? ''))) === 'yes';
                    ?>
                    <tr>
                        <td><input type="text" name="<?= esc($rp, 'attr') ?>[poste]" class="form-control form-control-sm" value="<?= esc((string) ($row['poste'] ?? '')) ?>"></td>
                        <td><input type="text" name="<?= esc($rp, 'attr') ?>[detail]" class="form-control form-control-sm" value="<?= esc((string) ($row['detail'] ?? '')) ?>"></td>
                        <td><input type="text" name="<?= esc($rp, 'attr') ?>[montant]" class="form-control form-control-sm" value="<?= esc((string) ($row['montant'] ?? '')) ?>"></td>
                        <td class="text-center"><input type="checkbox" name="<?= esc($rp, 'attr') ?>[is_total]" value="1" class="form-check-input" <?= $it ? 'checked' : '' ?>></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="form-text small mb-0">Cochez « Total » sur la ligne du total (classe <code>budget-total</code> côté site).</p>
    </div>
</div>
