<?php

declare(strict_types=1);

/** @var string $pfx */
/** @var array<string, mixed> $block */
/** @var string $helpLangKey */
/** @var string $titlePlaceholderLang */
/** @var string $subtitlePlaceholderLang */

$b = $block;
?>
<fieldset class="border rounded p-3 mb-3">
    <legend class="float-none w-auto px-2 small fw-semibold mb-0"><?= esc(lang('Admin.cms_sectors_banner_heading')) ?></legend>
    <p class="form-text small mb-2"><?= esc(lang($helpLangKey)) ?></p>
    <div class="mb-2">
        <label class="form-label small mb-1"><?= esc(lang('Admin.cms_sectors_banner_title')) ?></label>
        <input type="text" name="<?= esc($pfx, 'attr') ?>[banner_title]" class="form-control form-control-sm" value="<?= esc((string) ($b['banner_title'] ?? '')) ?>" placeholder="<?= esc(lang($titlePlaceholderLang), 'attr') ?>">
    </div>
    <div>
        <label class="form-label small mb-1"><?= esc(lang('Admin.cms_sectors_banner_subtitle')) ?></label>
        <input type="text" name="<?= esc($pfx, 'attr') ?>[banner_subtitle]" class="form-control form-control-sm" value="<?= esc((string) ($b['banner_subtitle'] ?? '')) ?>" placeholder="<?= esc(lang($subtitlePlaceholderLang), 'attr') ?>">
    </div>
</fieldset>
