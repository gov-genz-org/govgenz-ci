<?php

/** @var array<string, string> $sectors */
$sectors = $sectors ?? [];
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
                <form action="<?= esc(localized_site_url('join'), 'attr') ?>" method="post" accept-charset="UTF-8" class="ggz-form">
                    <?= csrf_field() ?>
                    <div class="ggz-field">
                        <label for="sector"><?= esc(lang('Site.join_sector_label')) ?></label>
                        <p class="ggz-field-hint" id="sector-hint"><?= esc(lang('Site.join_sector_hint')) ?></p>
                        <select name="sector" id="sector" required aria-describedby="sector-hint">
                            <option value=""><?= esc(lang('Site.join_sector_placeholder')) ?></option>
                            <?php foreach ($sectors as $key => $label) : ?>
                                <option value="<?= esc($key, 'attr') ?>" <?= old('sector') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <fieldset class="ggz-fieldset">
                        <legend class="ggz-fieldset-legend"><?= esc(lang('Site.join_fs_contact')) ?></legend>
                        <div class="ggz-field">
                            <label for="full_name"><?= esc(lang('Site.join_label_full_name')) ?></label>
                            <input type="text" name="full_name" id="full_name" value="<?= esc(old('full_name')) ?>" required autocomplete="name">
                        </div>
                        <div class="ggz-field">
                            <label for="email"><?= esc(lang('Site.join_label_email')) ?></label>
                            <input type="email" name="email" id="email" value="<?= esc(old('email')) ?>" required autocomplete="email" inputmode="email">
                        </div>
                    </fieldset>
                    <fieldset class="ggz-fieldset ggz-fieldset--optional">
                        <legend class="ggz-fieldset-legend"><?= esc(lang('Site.join_fs_optional_legend')) ?> <span class="ggz-optional-tag"><?= esc(lang('Site.join_optional_tag')) ?></span></legend>
                        <div class="ggz-field">
                            <label for="phone"><?= esc(lang('Site.join_label_phone')) ?></label>
                            <input type="text" name="phone" id="phone" value="<?= esc(old('phone')) ?>" autocomplete="tel" inputmode="tel">
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
