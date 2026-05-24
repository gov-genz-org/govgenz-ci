<?php

declare(strict_types=1);

helper(['locale', 'cms', 'asset', 'analytics']);

use App\Libraries\PublicNav;
use App\Libraries\SiteContext;
use App\Models\CmsPageModel;

$footerCmsPage = model(CmsPageModel::class)->getPublishedBySlug(cms_footer_embed_slug());
$footerCmsHtml = $footerCmsPage !== null ? trim(cms_render_page_body($footerCmsPage)) : '';
if ($footerCmsHtml !== '' && preg_match('/^<div\s+class="footer__columns(?:\s[^"]*)?"\s*>(.*)<\/div>$/is', $footerCmsHtml, $m) === 1) {
    $footerCmsHtml = trim((string) ($m[1] ?? ''));
}

?>
<!DOCTYPE html>
<html lang="<?= esc(SiteContext::locale()) ?>" data-theme="dark" class="ggz-public-theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0a14">
    <meta name="author" content="GoV Gen Z Madagascar">
    <title><?= esc($title ?? 'GovGenZ') ?></title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link rel="apple-touch-icon" href="/assets/logo-256.png">
    <?php if (! empty($metaDescription)) : ?>
        <meta name="description" content="<?= esc((string) $metaDescription, 'attr') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/govgenz-fonts.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/govgenz-tokens.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/govgenz-components.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/govgenz-template.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/govgenz-cms-shell.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/govgenz-front-pages.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/govgenz-bridge.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/ggz-legal-page.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/ggz-press-page.css'), 'attr') ?>">
    <?php if (analytics_is_active()) : ?>
        <link rel="stylesheet" href="<?= esc(public_asset_url('assets/css/cookie-consent.css'), 'attr') ?>">
    <?php endif; ?>
    <?= $extraHead ?? '' ?>
</head>
<body class="ggz-public-theme">
<a class="ggz-skip-link" href="#main-content"><?= esc(lang('Site.skip_to_content')) ?></a>
<header class="header" id="header">
    <div class="header__inner">
        <a href="<?= esc(localized_site_url(''), 'attr') ?>" class="header__logo">
            <img src="<?= base_url('assets/img/govgenz-logo.svg') ?>" alt="<?= esc(lang('Site.logo_alt'), 'attr') ?>" width="42" height="42" decoding="async" fetchpriority="high">
            <span class="header__title">
                <span>GoV Gen Z</span>
                <span class="header__title-sub">Madagascar</span>
            </span>
        </a>

        <?php
        $na          = $navActive ?? '';
        $navLinks    = SiteContext::navMainLinks();
        $switchHref  = locale_switch_url();
        $switchLabel = SiteContext::locale() === 'fr' ? 'EN' : 'FR';
        $switchTitle = SiteContext::locale() === 'fr' ? lang('Site.lang_switch_to_en') : lang('Site.lang_switch_to_fr');
        ?>
        <nav class="nav" id="nav" aria-label="<?= esc(lang('Site.nav_aria'), 'attr') ?>">
            <?= view('front/partials/nav_links', ['navLinks' => $navLinks, 'navActive' => $na]) ?>
        </nav>

        <div class="header__actions">
            <a href="<?= esc($switchHref, 'attr') ?>" class="lang-toggle lang-toggle--link" title="<?= esc($switchTitle, 'attr') ?>"><?= esc($switchLabel) ?></a>
            <button
                type="button"
                class="menu-toggle"
                id="menu-toggle"
                aria-expanded="false"
                aria-controls="nav"
                aria-label="<?= esc(lang('Site.menu_aria'), 'attr') ?>"
                data-label-open="<?= esc(lang('Site.menu_aria'), 'attr') ?>"
                data-label-close="<?= esc(lang('Site.menu_close_aria'), 'attr') ?>"
            >
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            </button>
        </div>
    </div>
</header>
<?php
$flashMsg  = session()->getFlashdata('message');
$flashErr  = session()->getFlashdata('error');
$flashErrs = session()->getFlashdata('errors');
$mainShell = trim('ggz-main-shell ' . trim((string) ($mainExtraClass ?? '')));
?>
<main id="main-content" class="<?= esc($mainShell, 'attr') ?>">
    <?php if (! empty($previewRibbon)) : ?>
        <div class="ggz-alert ggz-alert--info" role="status"><?= esc($previewRibbon) ?></div>
    <?php endif; ?>
    <?php if ($flashMsg) : ?>
        <div class="ggz-alert ggz-alert--success" role="status"><?= esc($flashMsg) ?></div>
    <?php endif; ?>
    <?php if ($flashErr) : ?>
        <div class="ggz-alert ggz-alert--danger" role="alert"><?= esc($flashErr) ?></div>
    <?php endif; ?>
    <?php if ($flashErrs) : ?>
        <div class="ggz-alert ggz-alert--danger" role="alert">
            <?php foreach ((array) $flashErrs as $err) : ?>
                <div class="ggz-alert__line"><?= esc(is_array($err) ? implode(' ', $err) : $err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?= $main ?? '' ?>
</main>

<footer class="footer">
    <div class="footer__inner">
        <div class="footer__brand">
            <img src="<?= base_url('assets/img/govgenz-logo.svg') ?>" alt="<?= esc(lang('Site.logo_alt'), 'attr') ?>" class="footer__logo" width="48" height="48" decoding="async">
            <div class="footer__brand-text">
                <div class="footer__brand-name">GoV Gen Z Madagascar</div>
                <div class="footer__brand-sub">Programme Paikady Taninjanaka</div>
            </div>
        </div>

        <div class="footer__columns">
            <?php if ($footerCmsPage !== null) : ?>
                <?= $footerCmsHtml ?>
            <?php else : ?>
                <?= view('front/partials/footer_columns_default') ?>
            <?php endif; ?>
        </div>

        <div class="footer__bottom">
            <div class="footer__devise"><?= esc(lang('Site.footer_devise')) ?></div>
            <div class="footer__legal">
                <span>&copy; <?= esc(date('Y')) ?> GoV Gen Z Madagascar</span>
                <span class="footer__sep">·</span>
                <span>govgenz.org · gzmada.org</span>
            </div>
        </div>
    </div>
</footer>

<?= view('front/partials/cookie_consent') ?>

<script defer src="<?= esc(public_asset_url('js/front/govgenz-template.js'), 'attr') ?>"></script>
<?php if (analytics_is_active()) : ?>
    <script defer src="<?= esc(public_asset_url('js/front/analytics-gtag.js'), 'attr') ?>"></script>
    <script defer src="<?= esc(public_asset_url('js/front/cookie-consent.js'), 'attr') ?>"></script>
<?php endif; ?>
<?= $extraScripts ?? '' ?>
</body>
</html>
