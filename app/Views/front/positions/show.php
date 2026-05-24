<?php

declare(strict_types=1);

helper(['position', 'language', 'project']);

use App\Models\PositionItemModel;

/** @var array<string, mixed> $item */
/** @var string $slug */
/** @var array<string, string> $sectorOptions */
/** @var array<string, string> $sectorFilterPills */
/** @var array<string, string> $typeLabels */
/** @var string $positionsListUrl */
/** @var string $bodyHtml */
/** @var string $shareUrl */
/** @var string $shareQrImageUrl */
/** @var string $shareQrPageUrl */
/** @var list<array{slug: string, title: string}> $relatedPositions */
/** @var list<array{label: string, href: string, variant: string}> $actionCtas */

$loc = \App\Libraries\SiteContext::locale();
$title    = (string) ($item['title'] ?? $slug);
$summary  = trim((string) ($item['summary'] ?? ''));
$excerpt  = trim((string) ($item['excerpt'] ?? ''));
$typesCsv = (string) ($item['types_csv'] ?? '');
$types    = position_types_from_csv($typesCsv);
$bandLabel = position_type_band_label($typesCsv);
$bandClass = position_type_band_class($typesCsv);
$dateLabel = position_format_published_date((string) ($item['published_at'] ?? ''), $loc);
$readLabel = position_reading_label($item, $loc);

$sectorCodes = [];
foreach (array_filter(array_map('trim', explode(',', (string) ($item['sectors_csv'] ?? '')))) as $code) {
    $c = strtolower($code);
    if ($c !== '') {
        $sectorCodes[$c] = true;
    }
}

$typeBadgeClass = static function (string $code): string {
    return match ($code) {
        PositionItemModel::TYPE_DENIAL   => 'positions-program-show__type-badge--denial',
        PositionItemModel::TYPE_PRAISE   => 'positions-program-show__type-badge--praise',
        PositionItemModel::TYPE_ANALYSIS => 'positions-program-show__type-badge--analysis',
        PositionItemModel::TYPE_SOLUTION => 'positions-program-show__type-badge--solution',
        default                          => '',
    };
};

?>
<article
    class="positions-program-show projects-program-show"
    aria-labelledby="position-show-heading"
    data-types-accent="<?= esc($bandClass, 'attr') ?>"
>

    <nav class="projects-program-show__breadcrumb" aria-label="<?= esc(lang('Positions.breadcrumb_aria'), 'attr') ?>">
        <a href="<?= esc($positionsListUrl, 'attr') ?>"><?= esc(lang('Positions.breadcrumb_list')) ?></a>
        <span aria-hidden="true">›</span>
        <span><?= esc($title) ?></span>
    </nav>

    <?php if ($bandLabel !== '') : ?>
        <div class="positions-program-show__band positions-program-show__band--<?= esc($bandClass, 'attr') ?>">
            <?= esc($bandLabel) ?>
        </div>
    <?php endif; ?>

    <header class="project-hero">
        <div class="project-hero-inner">
            <?php if ($sectorCodes !== [] || $types !== []) : ?>
                <div class="tags">
                    <?php foreach (array_slice(array_keys($sectorCodes), 0, 4) as $code) : ?>
                        <span class="tag"><?= esc($sectorFilterPills[$code] ?? $sectorOptions[$code] ?? $code) ?></span>
                    <?php endforeach; ?>
                    <?php foreach ($types as $tc) :
                        $tLab = $typeLabels[$tc] ?? $tc;
                        $tb   = $typeBadgeClass($tc);
                        ?>
                        <span class="positions-program-show__type-badge<?= $tb !== '' ? ' ' . esc($tb, 'attr') : '' ?>"><?= esc($tLab) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <h1 id="position-show-heading" class="section__title"><?= esc($title) ?></h1>
            <div class="project-hero-meta">
                <?php if ($dateLabel !== '') : ?>
                    <span>📅 <?= esc($dateLabel) ?></span>
                <?php endif; ?>
                <?php if ($readLabel !== '') : ?>
                    <span>⏱ <?= esc($readLabel) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($summary !== '') : ?>
                <p class="project-hero-desc"><?= nl2br(esc($summary)) ?></p>
            <?php elseif ($excerpt !== '') : ?>
                <p class="project-hero-desc"><?= esc($excerpt) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <div class="project-layout">
        <div class="position-main project-main">
            <?php if ($bodyHtml !== '') : ?>
                <?= $bodyHtml ?>
            <?php endif; ?>
        </div>

        <?= view('front/positions/partials/show_sidebar', [
            'item'              => $item,
            'title'             => $title,
            'slug'              => $slug,
            'types'             => $types,
            'typeLabels'        => $typeLabels,
            'sectorCodes'       => array_keys($sectorCodes),
            'sectorFilterPills' => $sectorFilterPills,
            'sectorOptions'     => $sectorOptions,
            'dateLabel'         => $dateLabel,
            'readLabel'         => $readLabel,
            'actionCtas'        => $actionCtas,
            'positionsListUrl'  => $positionsListUrl,
            'relatedPositions'  => $relatedPositions,
            'shareUrl'          => $shareUrl,
            'shareQrImageUrl'   => $shareQrImageUrl ?? '',
            'shareQrPageUrl'    => $shareQrPageUrl ?? '',
        ]) ?>
    </div>

</article>
