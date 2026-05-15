<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$members = $b['members'] ?? [];
if (! is_array($members)) {
    $members = [];
}
$members = array_values($members);
while (count($members) < 6) {
    $members[] = ['name' => '', 'role' => ''];
}
$members = array_slice($members, 0, 6);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0">Bloc · Équipe</span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove">Retirer</button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="team">
        <div class="mb-2">
            <label class="form-label small">Titre de section</label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Nom / rôle affiché</th><th>Sous-titre (petit)</th></tr></thead>
                <tbody>
                <?php foreach ($members as $mi => $m) : ?>
                    <?php
                    $m = is_array($m) ? $m : [];
                    $mp = $pfx . '[members][' . $mi . ']';
                    ?>
                    <tr>
                        <td><input type="text" name="<?= esc($mp, 'attr') ?>[name]" class="form-control form-control-sm" value="<?= esc((string) ($m['name'] ?? '')) ?>"></td>
                        <td><input type="text" name="<?= esc($mp, 'attr') ?>[role]" class="form-control form-control-sm" value="<?= esc((string) ($m['role'] ?? '')) ?>"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
