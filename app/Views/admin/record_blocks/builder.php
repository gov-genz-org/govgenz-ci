<?php

declare(strict_types=1);

/** @var string $contentMode */
/** @var list<array<string, mixed>> $blocksForForm */
/** @var bool $canUseAdvancedHtml */
/** @var string $ppLocale */
/** @var list<string> $allowedBlockTypes */
/** @var string $helpText */

$canUseAdvancedHtml = $canUseAdvancedHtml ?? false;
$ppLocale = in_array($ppLocale ?? 'fr', ['fr', 'en'], true) ? $ppLocale : 'fr';
$blocksForForm = $blocksForForm ?? [];
$allowedBlockTypes = $allowedBlockTypes ?? ['section_rich', 'note_panel', 'sources'];
$helpText = trim((string) ($helpText ?? lang('Admin.block_builder_help')));

$blockViews = [
    'section_rich'   => 'admin/project_projects/blocks/section_rich',
    'budget_table'   => 'admin/project_projects/blocks/budget_table',
    'material_needs' => 'admin/project_projects/blocks/material_needs',
    'timeline'       => 'admin/project_projects/blocks/timeline',
    'kpi_grid'       => 'admin/project_projects/blocks/kpi_grid',
    'impact_tracker' => 'admin/project_projects/blocks/impact_tracker',
    'note_panel'     => 'admin/project_projects/blocks/note_panel',
    'team'           => 'admin/project_projects/blocks/team',
    'sources'        => 'admin/project_projects/blocks/sources',
    'html'           => 'admin/project_projects/blocks/html_free',
];

$blockLabels = [
    'section_rich'   => lang('Admin.block_add_section'),
    'budget_table'   => lang('Admin.block_add_budget'),
    'material_needs' => lang('Admin.block_add_material'),
    'timeline'       => lang('Admin.block_add_timeline'),
    'kpi_grid'       => lang('Admin.block_add_kpi'),
    'impact_tracker' => lang('Admin.block_add_impact'),
    'note_panel'     => lang('Admin.block_add_note'),
    'team'           => lang('Admin.block_add_team'),
    'sources'        => lang('Admin.block_add_sources'),
    'html'           => lang('Admin.block_add_html'),
];

$allowedBlockTypes = array_values(array_unique(array_filter(
    $allowedBlockTypes,
    static fn ($type): bool => is_string($type) && isset($blockViews[$type])
)));

if (! $canUseAdvancedHtml) {
    $allowedBlockTypes = array_values(array_filter(
        $allowedBlockTypes,
        static fn (string $type): bool => $type !== 'html'
    ));
}

$mapBlockView = static function (array $block) use ($blockViews): string {
    $type = (string) ($block['type'] ?? 'section_rich');

    return $blockViews[$type] ?? $blockViews['section_rich'];
};
?>
<div id="pp-blocks-panel" class="<?= $contentMode === 'blocks' ? '' : 'd-none' ?>">
    <?php if ($helpText !== '') : ?>
        <p class="text-muted small mb-2"><?= esc($helpText) ?></p>
    <?php endif; ?>

    <div id="pp-blocks-container" class="mb-2">
        <?php foreach ($blocksForForm as $idx => $block) : ?>
            <?= view($mapBlockView(is_array($block) ? $block : []), [
                'i'        => $idx,
                'block'    => is_array($block) ? $block : [],
                'ppLocale' => $ppLocale,
            ]) ?>
        <?php endforeach; ?>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($allowedBlockTypes as $type) : ?>
            <button type="button" class="btn btn-sm <?= $type === 'html' ? 'btn-outline-secondary' : 'btn-outline-primary' ?>" data-pp-add="<?= esc($type, 'attr') ?>">
                <?= esc((string) ($blockLabels[$type] ?? $type)) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div id="pp-proto-store" class="d-none" aria-hidden="true">
    <?php foreach ($allowedBlockTypes as $type) : ?>
        <div data-pp-proto="<?= esc($type, 'attr') ?>">
            <?= view($blockViews[$type], [
                'i'        => '__I__',
                'block'    => ['type' => $type],
                'ppLocale' => $ppLocale,
            ]) ?>
        </div>
    <?php endforeach; ?>
</div>
