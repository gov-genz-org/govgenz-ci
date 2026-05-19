<?php

declare(strict_types=1);

helper(['analytics', 'locale']);

if (! analytics_is_active()) {
    return;
}

$cfg = analytics_config();
$privacyUrl = analytics_privacy_url();
?>
<div id="ggz-cookie-consent" class="ggz-cookie-consent" hidden aria-hidden="true" role="dialog" aria-labelledby="ggz-cookie-title" aria-describedby="ggz-cookie-desc" data-ga4-id="<?= esc($cfg->ga4MeasurementId, 'attr') ?>">
    <div class="ggz-cookie-consent__panel">
        <p id="ggz-cookie-title" class="ggz-cookie-consent__title"><?= esc(lang('Site.cookie_title')) ?></p>
        <p id="ggz-cookie-desc" class="ggz-cookie-consent__text"><?= esc(lang('Site.cookie_text')) ?></p>
        <ul class="ggz-cookie-consent__list">
            <li><?= esc(lang('Site.cookie_item_visitors')) ?></li>
            <li><?= esc(lang('Site.cookie_item_pages')) ?></li>
            <li><?= esc(lang('Site.cookie_item_device')) ?></li>
            <li><?= esc(lang('Site.cookie_item_source')) ?></li>
        </ul>
        <?php if ($privacyUrl !== null) : ?>
            <p class="ggz-cookie-consent__more">
                <a href="<?= esc($privacyUrl, 'attr') ?>"><?= esc(lang('Site.cookie_privacy_link')) ?></a>
            </p>
        <?php endif; ?>
        <div class="ggz-cookie-consent__actions">
            <button type="button" class="ggz-project-cta-btn ggz-project-cta-btn--ghost" data-ggz-consent="reject"><?= esc(lang('Site.cookie_reject')) ?></button>
            <button type="button" class="ggz-project-cta-btn ggz-project-cta-btn--teal" data-ggz-consent="accept"><?= esc(lang('Site.cookie_accept')) ?></button>
        </div>
    </div>
</div>
