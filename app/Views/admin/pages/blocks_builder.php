<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $blocksForForm */
$blockViews = [
    'section_text'    => 'admin/pages/block_section_text',
    'cards_grid'      => 'admin/pages/block_cards_grid',
    'stats_grid'      => 'admin/pages/block_stats_grid',
    'metrics_section' => 'admin/pages/block_stats_grid',
    'organization_hub'=> 'admin/pages/block_organization_hub',
    'contact_grid'    => 'admin/pages/block_contact_grid',
    'cta_panel'       => 'admin/pages/block_cta_panel',
    'legal_prose'     => 'admin/pages/block_legal_prose',
    'sources'         => 'admin/pages/block_sources',
    'sectors_grid'    => 'admin/pages/block_sectors_grid',
    'footer_columns'  => 'admin/pages/block_footer_columns',
    'html'            => 'admin/pages/block_html',
];

$blockLabels = [
    'section_text'     => lang('Admin.cms_add_section_text'),
    'cards_grid'       => lang('Admin.cms_add_cards_grid'),
    'stats_grid'       => lang('Admin.cms_add_stats_grid'),
    'organization_hub' => lang('Admin.cms_add_organization_hub'),
    'contact_grid'     => lang('Admin.cms_add_contact_grid'),
    'cta_panel'        => lang('Admin.cms_add_cta_panel'),
    'legal_prose'      => lang('Admin.cms_add_legal_prose'),
    'sources'          => lang('Admin.cms_add_sources'),
    'sectors_grid'     => lang('Admin.cms_add_sectors_grid'),
    'footer_columns'   => lang('Admin.cms_add_footer_columns'),
    'html'             => lang('Admin.cms_add_html'),
];

$allowedBlockTypes = [
    'section_text',
    'cards_grid',
    'stats_grid',
    'organization_hub',
    'contact_grid',
    'cta_panel',
    'legal_prose',
    'sources',
    'sectors_grid',
    'footer_columns',
    'html',
];

$mapBlockView = static function (array $block) use ($blockViews): string {
    $type = (string) ($block['type'] ?? 'section_text');
    if ($type === 'metrics_section') {
        $type = 'stats_grid';
    }

    return $blockViews[$type] ?? $blockViews['section_text'];
};
?>

<div id="cms-blocks-panel" class="<?= $contentMode === 'blocks' ? '' : 'd-none' ?>">
    <label class="form-label"><?= esc(lang('Admin.cms_blocks_label')) ?></label>
    <p class="text-muted small"><?= lang('Admin.cms_blocks_help', [
        site_url('admin/cms-guide-blocks'),
        site_url('admin/cms-guide-blocks#admin-block-footer_columns'),
    ]) ?></p>

    <div id="cms-blocks-container" class="mb-2">
        <?php foreach ($blocksForForm as $idx => $block) : ?>
            <?= view($mapBlockView(is_array($block) ? $block : []), [
                'i'     => $idx,
                'block' => is_array($block) ? $block : [],
            ]) ?>
        <?php endforeach; ?>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($allowedBlockTypes as $type) : ?>
            <button type="button" class="btn btn-sm <?= $type === 'html' ? 'btn-outline-secondary' : 'btn-outline-primary' ?>" data-cms-add="<?= esc($type, 'attr') ?>">
                <?= esc((string) ($blockLabels[$type] ?? $type)) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<div id="cms-proto-store" class="d-none" aria-hidden="true">
    <?php foreach ($allowedBlockTypes as $type) : ?>
        <div data-cms-proto="<?= esc($type, 'attr') ?>">
            <?= view($blockViews[$type], [
                'i'     => '__I__',
                'block' => ['type' => $type],
            ]) ?>
        </div>
    <?php endforeach; ?>
</div>
