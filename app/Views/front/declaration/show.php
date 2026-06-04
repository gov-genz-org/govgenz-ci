<?php

declare(strict_types=1);

helper(['declaration', 'language']);

use App\Models\DeclarationItemModel;

/** @var array<string, mixed> $item */
/** @var string $slug */
/** @var string $declarationListUrl */
/** @var string $shareUrl */
/** @var string $shareQrImageUrl */
/** @var string $shareQrPageUrl */
/** @var list<array{slug: string, title: string}> $relatedDeclarations */
/** @var list<array{label: string, href: string, variant: string}> $actionCtas */
/** @var string $bodyHtml */

$loc     = \App\Libraries\SiteContext::locale();
$kind    = (string) ($item['kind'] ?? DeclarationItemModel::KIND_OFFICIAL);
$tone    = declaration_kind_band_class($kind);
$band    = trim((string) ($item['band_label'] ?? ''));
if ($band === '') {
    $band = declaration_default_band_label($kind, $loc);
}
$title   = (string) ($item['title'] ?? $slug);
$summary = trim((string) ($item['summary'] ?? ''));
$meta    = declaration_format_published_meta((string) ($item['published_at'] ?? ''), (string) ($item['meta_line'] ?? ''), $loc);
$badge   = trim((string) ($item['badge_label'] ?? ''));
if ($badge === '') {
    $badge = declaration_default_band_label($kind, $loc);
}
$listSection = (string) ($item['list_section'] ?? DeclarationItemModel::SECTION_DECLARATIONS);
$sectionLabel = $listSection === DeclarationItemModel::SECTION_PARTNERSHIPS
    ? lang('Declaration.section_partnerships_label')
    : lang('Declaration.section_declarations_label');

?>
<article
    class="declaration-program-show projects-program-show"
    aria-labelledby="declaration-show-heading"
    data-kind-accent="<?= esc($tone, 'attr') ?>"
>

    <nav class="projects-program-show__breadcrumb" aria-label="<?= esc(lang('Declaration.breadcrumb_aria'), 'attr') ?>">
        <a href="<?= esc($declarationListUrl, 'attr') ?>"><?= esc(lang('Declaration.breadcrumb_list')) ?></a>
        <span aria-hidden="true">›</span>
        <span><?= esc($title) ?></span>
    </nav>

    <?php if ($band !== '') : ?>
        <div class="declaration-program-show__band declaration-program-show__band--<?= esc($tone, 'attr') ?>">
            <?= esc($band) ?>
        </div>
    <?php endif; ?>

    <header class="project-hero">
        <div class="project-hero-inner">
            <div class="tags">
                <span class="declaration-program-show__kind-badge declaration-program-show__kind-badge--<?= esc($tone, 'attr') ?>"><?= esc($badge) ?></span>
            </div>
            <h1 id="declaration-show-heading" class="section__title"><?= esc($title) ?></h1>
            <div class="project-hero-meta">
                <?php if ($meta !== '') : ?>
                    <span>📅 <?= esc($meta) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($summary !== '') : ?>
                <p class="project-hero-desc"><?= nl2br(esc($summary)) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <div class="project-layout">
        <div class="declaration-main project-main">
            <?php if (($bodyHtml ?? '') !== '') : ?>
                <?= $bodyHtml ?>
            <?php endif; ?>
        </div>

        <?= view('front/declaration/partials/show_sidebar', [
            'item'                 => $item,
            'title'                => $title,
            'slug'                 => $slug,
            'kind'                 => $kind,
            'tone'                 => $tone,
            'kindLabel'            => declaration_default_band_label($kind, $loc),
            'sectionLabel'         => $sectionLabel,
            'dateLabel'            => $meta,
            'badgeLabel'           => $badge,
            'actionCtas'           => $actionCtas,
            'declarationListUrl'   => $declarationListUrl,
            'relatedDeclarations'  => $relatedDeclarations,
            'shareUrl'             => $shareUrl,
            'shareQrImageUrl'      => $shareQrImageUrl ?? '',
            'shareQrPageUrl'       => $shareQrPageUrl ?? '',
        ]) ?>
    </div>

</article>
