<?php

declare(strict_types=1);

helper('admin');

/** @var int|string $i */
/** @var array<string, mixed> $block */

$pfx = 'blocks[' . $i . ']';
$b = $block;
$members = admin_pp_repeat_object_rows(
    is_array($b['members'] ?? null) ? $b['members'] : [],
    static fn (array $m): bool => trim((string) ($m['name'] ?? '')) === '' && trim((string) ($m['role'] ?? '')) === '',
    ['name' => '', 'role' => ''],
);
?>
<div class="project-block-row card mb-3 border-secondary">
    <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold small mb-0"><?= esc(lang('Admin.block_type_team')) ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger project-block-remove"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="card-body">
        <input type="hidden" name="<?= esc($pfx, 'attr') ?>[type]" value="team">
        <div class="mb-2">
            <label class="form-label small"><?= esc(lang('Admin.block_section_title')) ?></label>
            <input type="text" name="<?= esc($pfx, 'attr') ?>[section_title]" class="form-control form-control-sm" maxlength="255" value="<?= esc((string) ($b['section_title'] ?? '')) ?>">
        </div>
        <div class="pp-repeatable" data-pp-repeat-key="members">
            <div class="row g-2 mb-1 small fw-semibold text-muted d-none d-md-flex align-items-center">
                <div class="col-md"><?= esc(lang('Admin.block_row_name_role')) ?></div>
                <div class="col-md"><?= esc(lang('Admin.block_row_subtitle_small')) ?></div>
                <div class="col-auto ms-auto" style="width:2.75rem"></div>
            </div>
            <div class="pp-repeat-body">
                    <?php foreach ($members as $mi => $m) : ?>
                        <?= view('admin/project_projects/blocks/team_member_row', [
                            'rp' => $pfx . '[members][' . $mi . ']',
                            'm'  => is_array($m) ? $m : [],
                        ]) ?>
                    <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary pp-repeat-add mt-2"><?= esc(lang('Admin.block_add_member')) ?></button>
            <template class="pp-repeat-template">
                <?= view('admin/project_projects/blocks/team_member_row', [
                    'rp' => $pfx . '[members][__RI__]',
                    'm'  => ['name' => '', 'role' => ''],
                ]) ?>
            </template>
        </div>
    </div>
</div>
