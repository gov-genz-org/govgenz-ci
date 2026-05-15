<?php

/** @var array<string, string> $sectors */
$sectors = $sectors ?? [];

/** @var list<string> $preselectedSectors */
$preselectedSectors = $preselectedSectors ?? [];

/** @var array<string, string> Erreurs validation (flash) — lues ici pour affichage inline ; le layout ne les redouble pas */
$joinErrors = session()->getFlashdata('errors');
if (! is_array($joinErrors)) {
    $joinErrors = [];
}

$oldSectorRaw = old('sector');
$oldSectors = [];
if (is_array($oldSectorRaw)) {
    $oldSectors = array_values(array_map(static fn ($v) => (string) $v, $oldSectorRaw));
} elseif (is_string($oldSectorRaw) && trim($oldSectorRaw) !== '') {
    $oldSectors = [trim($oldSectorRaw)];
}

$selectedSectors = $oldSectors !== [] ? $oldSectors : $preselectedSectors;

$joinPhoneMsgs = [
    'generic' => lang('Site.join_phone_invalid'),
    '1'       => lang('Site.join_phone_err_country'),
    '2'       => lang('Site.join_phone_err_short'),
    '3'       => lang('Site.join_phone_err_long'),
    '4'       => lang('Site.join_phone_err_local'),
    '5'       => lang('Site.join_phone_err_length'),
];
$joinPhoneMsgsJson = json_encode($joinPhoneMsgs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

$sectorErr = isset($joinErrors['sector']) ? (string) $joinErrors['sector'] : '';
$fullNameErr = isset($joinErrors['full_name']) ? (string) $joinErrors['full_name'] : '';
$emailErr = isset($joinErrors['email']) ? (string) $joinErrors['email'] : '';
$phoneCountryErr = isset($joinErrors['phone_country']) ? (string) $joinErrors['phone_country'] : '';
?>
<div class="wysiwyg ggz-shell-wysiwyg ggz-cms-fullwidth">
    <section class="section section--join" aria-labelledby="join-heading">
        <div class="section__inner">
            <div class="section__header">
                <div class="section__overline"><?= esc(lang('Site.join_overline')) ?></div>
                <h1 class="section__title" id="join-heading"><?= esc(lang('Site.breadcrumb_join')) ?></h1>
                <p class="section__lead">
                    <?= esc(lang('Site.join_intro')) ?>
                </p>
            </div>

            <div class="ggz-page-join">
                <form
                    action="<?= esc(localized_site_url('join'), 'attr') ?>"
                    method="post"
                    accept-charset="UTF-8"
                    class="ggz-form"
                    novalidate
                    data-phone-msgs="<?= esc($joinPhoneMsgsJson, 'attr') ?>"
                    data-join-msg-sector="<?= esc(lang('Site.join_sector_required'), 'attr') ?>"
                    data-join-msg-full-name="<?= esc(lang('Site.join_err_full_name_required'), 'attr') ?>"
                    data-join-msg-email-req="<?= esc(lang('Site.join_err_email_required'), 'attr') ?>"
                    data-join-msg-email-invalid="<?= esc(lang('Site.join_err_email_invalid'), 'attr') ?>"
                >
                    <?= csrf_field() ?>
                    <div class="ggz-field ggz-field-sector">
                        <label for="sector"><?= esc(lang('Site.join_sector_label')) ?></label>
                        <p class="ggz-field-hint" id="sector-hint"><?= esc(lang('Site.join_sector_hint')) ?></p>
                        <select
                            id="sector"
                            name="sector[]"
                            multiple
                            required
                            aria-describedby="sector-hint sector-error"
                            data-multi-select
                            data-placeholder="<?= esc(lang('Site.join_sector_placeholder'), 'attr') ?>"
                            data-theme="dark"
                            data-search="true"
                            data-select-all="true"
                            data-min="1"
                            data-required="true"
                            data-list-all="true"
                            data-close-list-on-item-select="false"
                        >
                            <?php foreach ($sectors as $key => $label) : ?>
                                <option value="<?= esc($key, 'attr') ?>" <?= in_array($key, $selectedSectors, true) ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="ggz-field-error" id="sector-error" role="alert" <?= $sectorErr === '' ? 'hidden' : '' ?>><?= esc($sectorErr) ?></p>
                    </div>
                    <fieldset class="ggz-fieldset">
                        <legend class="ggz-fieldset-legend"><?= esc(lang('Site.join_fs_contact')) ?></legend>
                        <div class="ggz-field">
                            <label for="full_name"><?= esc(lang('Site.join_label_full_name')) ?></label>
                            <input type="text" name="full_name" id="full_name" value="<?= esc(old('full_name')) ?>" required autocomplete="name" aria-describedby="full_name-error">
                            <p class="ggz-field-error" id="full_name-error" role="alert" <?= $fullNameErr === '' ? 'hidden' : '' ?>><?= esc($fullNameErr) ?></p>
                        </div>
                        <div class="ggz-field">
                            <label for="email"><?= esc(lang('Site.join_label_email')) ?></label>
                            <input type="email" name="email" id="email" value="<?= esc(old('email')) ?>" required autocomplete="email" inputmode="email" aria-describedby="email-error">
                            <p class="ggz-field-error" id="email-error" role="alert" <?= $emailErr === '' ? 'hidden' : '' ?>><?= esc($emailErr) ?></p>
                        </div>
                    </fieldset>
                    <fieldset class="ggz-fieldset ggz-fieldset--optional">
                        <legend class="ggz-fieldset-legend"><?= esc(lang('Site.join_fs_optional_legend')) ?> <span class="ggz-optional-tag"><?= esc(lang('Site.join_optional_tag')) ?></span></legend>
                        <div class="ggz-field ggz-field-phone">
                            <label for="phone"><?= esc(lang('Site.join_label_phone')) ?></label>
                            <p class="ggz-field-hint" id="phone-hint"><?= esc(lang('Site.join_phone_placeholder_hint')) ?></p>
                            <input type="hidden" name="phone_country" id="phone_country" value="<?= esc((string) old('phone_country', '+261'), 'attr') ?>">
                            <input type="tel" name="phone_number" id="phone" value="<?= esc(old('phone_number')) ?>" autocomplete="tel-national" inputmode="tel" aria-describedby="phone-hint phone-error phone_country-error">
                            <p class="ggz-field-error" id="phone_country-error" role="alert" <?= $phoneCountryErr === '' ? 'hidden' : '' ?>><?= esc($phoneCountryErr) ?></p>
                            <p class="ggz-field-error" id="phone-error" role="alert" hidden></p>
                        </div>
                        <div class="ggz-field">
                            <label for="message"><?= esc(lang('Site.join_label_message')) ?></label>
                            <textarea name="message" id="message" rows="5"><?= esc(old('message')) ?></textarea>
                        </div>
                    </fieldset>
                    <div class="ggz-form-actions">
                        <button type="submit" class="btn btn--primary"><?= esc(lang('Site.join_submit')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
