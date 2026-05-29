<?php

declare(strict_types=1);

/** @var string $name */
/** @var array<string, mixed> $column */

$column = is_array($column ?? null) ? $column : [];
$links = $column['links'] ?? [];
if (! is_array($links)) {
    $links = [];
}
$links = array_values(array_filter($links, static function ($link): bool {
    if (! is_array($link)) {
        return false;
    }

    return trim((string) ($link['label'] ?? '')) !== '';
}));
?>
<div class="cms-repeat-row border rounded p-2 bg-light-subtle">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
        <span class="small fw-semibold"><?= esc(lang('Admin.cms_footer_column_heading')) ?></span>
        <?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?>
    </div>
    <input type="text" name="<?= esc($name, 'attr') ?>[title]" class="form-control form-control-sm mb-2" value="<?= esc((string) ($column['title'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_footer_column_title'), 'attr') ?>">
    <div class="cms-repeatable" data-cms-repeat-key="links">
        <p class="small text-muted mb-1"><?= esc(lang('Admin.cms_footer_links_heading')) ?></p>
        <div class="cms-repeat-body">
            <?php foreach ($links as $li => $link) : ?>
                <?= view('admin/pages/partials/footer_link_row', [
                    'name' => $name . '[links][' . $li . ']',
                    'link' => is_array($link) ? $link : [],
                ]) ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary cms-repeat-add mt-2"><?= esc(lang('Admin.cms_add_footer_link')) ?></button>
        <template class="cms-repeat-template">
            <?= view('admin/pages/partials/footer_link_row', [
                'name' => $name . '[links][__RI__]',
                'link' => [],
            ]) ?>
        </template>
    </div>
</div>
