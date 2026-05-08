<?php

declare(strict_types=1);

helper(['locale', 'cms']);

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
    <link rel="icon" type="image/png" href="<?= base_url('assets/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/logo-256.png') ?>">
    <?php if (! empty($metaDescription)) : ?>
        <meta name="description" content="<?= esc((string) $metaDescription, 'attr') ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Fraunces:ital,wght@0,400;0,600;0,800;0,900;1,400&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/govgenz-tokens.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/govgenz-template.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/govgenz-front-pages.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/govgenz-bridge.css') ?>">
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

        <nav class="nav" id="nav" aria-label="<?= esc(lang('Site.nav_aria'), 'attr') ?>">
        <?php
        $na           = $navActive ?? '';
        $seg1         = SiteContext::publicSegment(1);
        $seg2         = SiteContext::publicSegment(2);
        $navLinks     = SiteContext::navMainLinks();
        $switchHref   = locale_switch_url();
        $switchLabel  = SiteContext::locale() === 'fr' ? 'EN' : 'FR';
        $switchTitle  = SiteContext::locale() === 'fr' ? lang('Site.lang_switch_to_en') : lang('Site.lang_switch_to_fr');

        foreach ($navLinks as $link) :
            $active = PublicNav::isActive($link['match_key'], $na, $seg1, $seg2);
            $parts  = array_values(array_filter([
                $active ? 'is-active' : null,
                ($link['css_class'] ?? '') !== '' ? trim((string) $link['css_class']) : null,
            ], static fn ($x) => $x !== null && $x !== ''));
            $linkClass = $parts !== [] ? implode(' ', $parts) : '';
            ?>
            <a href="<?= esc($link['href'], 'attr') ?>"<?= $linkClass !== '' ? ' class="' . esc($linkClass, 'attr') . '"' : '' ?>><?= esc($link['label']) ?></a>
        <?php endforeach; ?>
        </nav>

        <div class="header__actions">
            <a href="<?= esc($switchHref, 'attr') ?>" class="lang-toggle lang-toggle--link" title="<?= esc($switchTitle, 'attr') ?>"><?= esc($switchLabel) ?></a>
            <button type="button" class="menu-toggle" id="menu-toggle" aria-label="<?= esc(lang('Site.menu_aria'), 'attr') ?>">
                <span></span><span></span><span></span>
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

<script defer src="<?= base_url('js/front/govgenz-template.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
