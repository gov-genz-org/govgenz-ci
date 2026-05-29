<?php

declare(strict_types=1);

/** @var string $name */
/** @var array<string, mixed> $card */

helper('cms');

$card = is_array($card ?? null) ? $card : [];
$bulletsText = (string) ($card['bullets_text'] ?? (is_array($card['bullets'] ?? null) ? implode("\n", $card['bullets']) : ''));
$mediaId = (int) ($card['media_id'] ?? 0);
$mediaPreviewUrl = $mediaId > 0 ? cms_media_public_url($mediaId) : null;
$mediaId2 = (int) ($card['media_id_2'] ?? 0);
$mediaPreviewUrl2 = $mediaId2 > 0 ? cms_media_public_url($mediaId2) : null;
?>
<div class="cms-repeat-row border rounded p-2 bg-light-subtle">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
        <span class="small fw-semibold"><?= esc(lang('Admin.cms_card_title')) ?></span>
        <?= view('admin/pages/partials/repeat_remove_button', ['title' => lang('Admin.block_remove_line')]) ?>
    </div>
    <div class="row g-2">
        <div class="col-md-3">
            <input type="text" name="<?= esc($name, 'attr') ?>[eyebrow]" class="form-control form-control-sm mb-2 cms-card-field-wrap" data-cms-card-field="eyebrow" value="<?= esc((string) ($card['eyebrow'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_card_eyebrow'), 'attr') ?>">
            <input type="text" name="<?= esc($name, 'attr') ?>[value]" class="form-control form-control-sm mb-2 cms-card-field-wrap" data-cms-card-field="value" value="<?= esc((string) ($card['value'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_card_value'), 'attr') ?>">
            <input type="text" name="<?= esc($name, 'attr') ?>[unit]" class="form-control form-control-sm cms-card-field-wrap" data-cms-card-field="unit" value="<?= esc((string) ($card['unit'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_card_unit'), 'attr') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="<?= esc($name, 'attr') ?>[title]" class="form-control form-control-sm mb-2 cms-card-field-wrap" data-cms-card-field="title" value="<?= esc((string) ($card['title'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_card_title'), 'attr') ?>">
            <input type="text" name="<?= esc($name, 'attr') ?>[subtitle]" class="form-control form-control-sm cms-card-field-wrap" data-cms-card-field="subtitle" value="<?= esc((string) ($card['subtitle'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.block_row_subtitle'), 'attr') ?>">
        </div>
        <div class="col-md-3">
            <textarea name="<?= esc($name, 'attr') ?>[description]" class="form-control form-control-sm mb-2 cms-card-field-wrap" data-cms-card-field="description" rows="2" placeholder="<?= esc(lang('Admin.cms_card_description'), 'attr') ?>"><?= esc((string) ($card['description'] ?? '')) ?></textarea>
            <textarea name="<?= esc($name, 'attr') ?>[bullets_text]" class="form-control form-control-sm cms-card-field-wrap" data-cms-card-field="bullets" rows="2" placeholder="<?= esc(lang('Admin.cms_card_bullets'), 'attr') ?>"><?= esc($bulletsText) ?></textarea>
        </div>
        <div class="col-md-3">
            <input type="text" name="<?= esc($name, 'attr') ?>[href]" class="form-control form-control-sm mb-2 cms-card-field-wrap" data-cms-card-field="href" value="<?= esc((string) ($card['href'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.ph_cms_action_href'), 'attr') ?>">
            <div class="cms-media-slot mb-2 cms-card-field-wrap" data-cms-card-field="media">
                <div class="input-group input-group-sm mb-2">
                    <input type="number" name="<?= esc($name, 'attr') ?>[media_id]" class="form-control cms-media-id-input" min="1" step="1" readonly value="<?= esc($mediaId > 0 ? (string) $mediaId : '') ?>" placeholder="<?= esc(lang('Admin.cms_card_media_id'), 'attr') ?>">
                    <button type="button" class="btn btn-outline-secondary cms-pick-media"><?= esc(lang('Admin.cms_pick_media')) ?></button>
                    <button type="button" class="btn btn-outline-danger cms-clear-media"><?= esc(lang('Admin.block_remove')) ?></button>
                </div>
                <div class="cms-media-preview <?= $mediaPreviewUrl !== null ? '' : 'd-none' ?> border rounded bg-white p-1 mb-2">
                    <img src="<?= esc((string) ($mediaPreviewUrl ?? ''), 'attr') ?>" alt="" loading="lazy">
                </div>
                <input type="text" name="<?= esc($name, 'attr') ?>[media_alt]" class="form-control form-control-sm cms-media-alt-input" value="<?= esc((string) ($card['media_alt'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_card_media_alt'), 'attr') ?>">
            </div>
            <div class="cms-media-slot mb-2 cms-card-field-wrap" data-cms-card-field="media2">
                <div class="input-group input-group-sm mb-2">
                    <input type="number" name="<?= esc($name, 'attr') ?>[media_id_2]" class="form-control cms-media-id-input" min="1" step="1" readonly value="<?= esc($mediaId2 > 0 ? (string) $mediaId2 : '') ?>" placeholder="<?= esc(lang('Admin.cms_card_media_id_2'), 'attr') ?>">
                    <button type="button" class="btn btn-outline-secondary cms-pick-media"><?= esc(lang('Admin.cms_pick_media')) ?></button>
                    <button type="button" class="btn btn-outline-danger cms-clear-media"><?= esc(lang('Admin.block_remove')) ?></button>
                </div>
                <div class="cms-media-preview <?= $mediaPreviewUrl2 !== null ? '' : 'd-none' ?> border rounded bg-white p-1 mb-2">
                    <img src="<?= esc((string) ($mediaPreviewUrl2 ?? ''), 'attr') ?>" alt="" loading="lazy">
                </div>
                <input type="text" name="<?= esc($name, 'attr') ?>[media_alt_2]" class="form-control form-control-sm cms-media-alt-input" value="<?= esc((string) ($card['media_alt_2'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_card_media_alt_2'), 'attr') ?>">
            </div>
            <input type="text" name="<?= esc($name, 'attr') ?>[icon_url]" class="form-control form-control-sm cms-card-field-wrap" data-cms-card-field="icon_url" value="<?= esc((string) ($card['icon_url'] ?? '')) ?>" placeholder="<?= esc(lang('Admin.cms_card_icon_url'), 'attr') ?>">
        </div>
    </div>
</div>
