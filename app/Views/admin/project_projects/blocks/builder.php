<?php

declare(strict_types=1);

/** @var string $contentMode */
/** @var list<array<string, mixed>> $blocksForForm */
/** @var bool $canUseAdvancedHtml */
/** @var string $ppLocale */
$recordBlocksConfig = config('AdminRecordBlocks');
?>
<?= view('admin/record_blocks/builder', [
    'contentMode'        => $contentMode,
    'blocksForForm'      => $blocksForForm,
    'canUseAdvancedHtml' => $canUseAdvancedHtml ?? false,
    'ppLocale'           => $ppLocale ?? 'fr',
    'helpText'           => lang('Admin.block_builder_help'),
    'allowedBlockTypes'  => $recordBlocksConfig->project,
]) ?>
