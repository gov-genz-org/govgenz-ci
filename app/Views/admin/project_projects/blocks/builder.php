<?php

declare(strict_types=1);

/** @var string $contentMode */
/** @var list<array<string, mixed>> $blocksForForm */
/** @var bool $canUseAdvancedHtml */
/** @var string $ppLocale */
$canUseAdvancedHtml = $canUseAdvancedHtml ?? false;
$ppLocale = in_array($ppLocale ?? 'fr', ['fr', 'en'], true) ? $ppLocale : 'fr';

/**
 * @param array<string, mixed> $block
 */
$mapBlockView = static function (array $block): string {
    $t = (string) ($block['type'] ?? 'section_rich');

    return match ($t) {
        'budget_table' => 'admin/project_projects/blocks/budget_table',
        'material_needs' => 'admin/project_projects/blocks/material_needs',
        'timeline' => 'admin/project_projects/blocks/timeline',
        'kpi_grid' => 'admin/project_projects/blocks/kpi_grid',
        'impact_tracker' => 'admin/project_projects/blocks/impact_tracker',
        'note_panel' => 'admin/project_projects/blocks/note_panel',
        'team' => 'admin/project_projects/blocks/team',
        'sources' => 'admin/project_projects/blocks/sources',
        'html' => 'admin/project_projects/blocks/html_free',
        default => 'admin/project_projects/blocks/section_rich',
    };
};
?>
<div id="pp-blocks-panel" class="<?= $contentMode === 'blocks' ? '' : 'd-none' ?>">
    <p class="text-muted small mb-2"><?= esc(lang('Admin.block_builder_help')) ?></p>

    <div id="pp-blocks-container" class="mb-2">
        <?php foreach ($blocksForForm as $idx => $block) : ?>
            <?= view($mapBlockView(is_array($block) ? $block : []), [
                'i'        => $idx,
                'block'    => is_array($block) ? $block : [],
                'ppLocale' => $ppLocale ?? 'fr',
            ]) ?>
        <?php endforeach; ?>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="section_rich"><?= esc(lang('Admin.block_add_section')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="budget_table"><?= esc(lang('Admin.block_add_budget')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="material_needs"><?= esc(lang('Admin.block_add_material')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="timeline"><?= esc(lang('Admin.block_add_timeline')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="kpi_grid"><?= esc(lang('Admin.block_add_kpi')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="impact_tracker"><?= esc(lang('Admin.block_add_impact')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="note_panel"><?= esc(lang('Admin.block_add_note')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="team"><?= esc(lang('Admin.block_add_team')) ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-pp-add="sources"><?= esc(lang('Admin.block_add_sources')) ?></button>
        <?php if ($canUseAdvancedHtml) : ?>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-pp-add="html"><?= esc(lang('Admin.block_add_html')) ?></button>
        <?php endif; ?>
    </div>
</div>

<div id="pp-proto-store" class="d-none" aria-hidden="true">
    <div data-pp-proto="section_rich"><?= view('admin/project_projects/blocks/section_rich', ['i' => '__I__', 'block' => ['type' => 'section_rich']]) ?></div>
    <div data-pp-proto="budget_table"><?= view('admin/project_projects/blocks/budget_table', ['i' => '__I__', 'block' => ['type' => 'budget_table'], 'ppLocale' => $ppLocale]) ?></div>
    <div data-pp-proto="material_needs"><?= view('admin/project_projects/blocks/material_needs', ['i' => '__I__', 'block' => ['type' => 'material_needs']]) ?></div>
    <div data-pp-proto="timeline"><?= view('admin/project_projects/blocks/timeline', ['i' => '__I__', 'block' => ['type' => 'timeline']]) ?></div>
    <div data-pp-proto="kpi_grid"><?= view('admin/project_projects/blocks/kpi_grid', ['i' => '__I__', 'block' => ['type' => 'kpi_grid']]) ?></div>
    <div data-pp-proto="impact_tracker"><?= view('admin/project_projects/blocks/impact_tracker', ['i' => '__I__', 'block' => ['type' => 'impact_tracker']]) ?></div>
    <div data-pp-proto="note_panel"><?= view('admin/project_projects/blocks/note_panel', ['i' => '__I__', 'block' => ['type' => 'note_panel']]) ?></div>
    <div data-pp-proto="team"><?= view('admin/project_projects/blocks/team', ['i' => '__I__', 'block' => ['type' => 'team']]) ?></div>
    <div data-pp-proto="sources"><?= view('admin/project_projects/blocks/sources', ['i' => '__I__', 'block' => ['type' => 'sources']]) ?></div>
    <div data-pp-proto="html"><?= view('admin/project_projects/blocks/html_free', ['i' => '__I__', 'block' => ['type' => 'html']]) ?></div>
</div>
