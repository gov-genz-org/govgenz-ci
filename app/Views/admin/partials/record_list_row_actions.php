<?php

declare(strict_types=1);

helper('form');

/** @var string|null $previewUrl */
/** @var string $editUrl */
/** @var string|null $duplicateUrl */
/** @var string $deleteUrl */
/** @var string $deleteConfirmMessage */
/** @var bool $duplicateTradDisabled */
$duplicateTradDisabled = (bool) ($duplicateTradDisabled ?? false);
$showDuplicateTrad     = (bool) ($showDuplicateTrad ?? (trim((string) ($duplicateUrl ?? '')) !== ''));
?>
<div class="text-end text-nowrap">
    <?php if ($previewUrl !== null && $previewUrl !== '') : ?>
        <a href="<?= esc($previewUrl, 'attr') ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><?= esc(lang('Admin.action_view')) ?></a>
    <?php endif; ?>
    <a href="<?= esc($editUrl, 'attr') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_edit')) ?></a>
    <?php if ($showDuplicateTrad) : ?>
        <form action="<?= esc($duplicateUrl, 'attr') ?>" method="post" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-primary btn-sm" <?= $duplicateTradDisabled ? 'disabled title="' . esc(lang('Admin.tooltip_duplicate_trad_disabled'), 'attr') . '"' : '' ?>><?= esc(lang('Admin.action_duplicate_trad')) ?></button>
        </form>
    <?php endif; ?>
    <form action="<?= esc($deleteUrl, 'attr') ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc($deleteConfirmMessage, 'attr') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Admin.action_delete')) ?></button>
    </form>
</div>
