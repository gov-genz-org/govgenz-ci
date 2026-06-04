<?php

declare(strict_types=1);

/** @var string $idInputName */
/** @var string $altInputName */
/** @var int $mediaId */
/** @var string $mediaAlt */
/** @var string|null $labelKey */
/** @var string|null $helpKey */

helper('cms');

$mediaId = (int) ($mediaId ?? 0);
$mediaAlt = (string) ($mediaAlt ?? '');
$labelKey = (string) ($labelKey ?? 'Admin.form_structure_icon');
$helpKey = (string) ($helpKey ?? 'Admin.help_structure_icon');
$mediaPreviewUrl = $mediaId > 0 ? cms_media_public_url($mediaId) : null;
?>
<div class="cms-media-slot">
    <label class="form-label"><?= esc(lang($labelKey)) ?></label>
    <div class="input-group input-group-sm mb-2">
        <input type="number" name="<?= esc($idInputName, 'attr') ?>" class="form-control cms-media-id-input" min="1" step="1" readonly value="<?= esc($mediaId > 0 ? (string) $mediaId : '') ?>" placeholder="<?= esc(lang('Admin.cms_card_media_id'), 'attr') ?>">
        <button type="button" class="btn btn-outline-secondary cms-pick-media"><?= esc(lang('Admin.cms_pick_media')) ?></button>
        <button type="button" class="btn btn-outline-danger cms-clear-media"><?= esc(lang('Admin.block_remove')) ?></button>
    </div>
    <div class="cms-media-preview <?= $mediaPreviewUrl !== null ? '' : 'd-none' ?> border rounded bg-white p-1 mb-2" style="max-width: 6rem;">
        <img src="<?= esc((string) ($mediaPreviewUrl ?? ''), 'attr') ?>" alt="" loading="lazy" class="img-fluid">
    </div>
    <input type="text" name="<?= esc($altInputName, 'attr') ?>" class="form-control form-control-sm cms-media-alt-input" value="<?= esc($mediaAlt) ?>" placeholder="<?= esc(lang('Admin.cms_card_media_alt'), 'attr') ?>">
    <div class="form-text"><?= esc(lang($helpKey)) ?></div>
</div>
