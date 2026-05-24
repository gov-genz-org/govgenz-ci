<?php

declare(strict_types=1);

use App\Libraries\SiteContext;
use App\Models\PositionItemModel;

helper(['position', 'language']);

/** @var list<array<string, mixed>> $positions */
/** @var array<string, string> $sectorOptions */
/** @var array<string, string> $sectorFilterPills */
/** @var array<string, string> $typeLabels */
/** @var list<string> $filterTypes */
/** @var list<string> $filterSectors */
/** @var array{published_count: int, sectors_covered: int, types_count: int} $stats */
/** @var string $positionsListUrl */
/** @var string $filterPostUrl */
/** @var string $csrfTokenName */
/** @var string $csrfHash */
/** @var string $heroOverline */
/** @var string $heroTitle */
/** @var string $heroLead */

$loc = SiteContext::locale();
$filtersActive = $filterTypes !== [] || $filterSectors !== [];
$shownCount    = count($positions);

$typeTips = [
    PositionItemModel::TYPE_DENIAL   => lang('Positions.type_tip_denial'),
    PositionItemModel::TYPE_PRAISE   => lang('Positions.type_tip_praise'),
    PositionItemModel::TYPE_ANALYSIS => lang('Positions.type_tip_analysis'),
    PositionItemModel::TYPE_SOLUTION => lang('Positions.type_tip_solution'),
];
?>
<article
    class="wysiwyg ggz-cms-fullwidth ggz-shell-wysiwyg positions-program-page js-positions-program-root"
    aria-labelledby="positions-program-heading"
    data-filter-endpoint="<?= esc($filterPostUrl, 'attr') ?>"
    data-csrf-name="<?= esc($csrfTokenName, 'attr') ?>"
    data-csrf-hash="<?= esc($csrfHash, 'attr') ?>"
    data-csrf-header="<?= esc(csrf_header(), 'attr') ?>"
    data-ajax-error="<?= esc(lang('Positions.ajax_error'), 'attr') ?>"
    data-ajax-csrf-reload="<?= esc(lang('Positions.ajax_csrf_reload'), 'attr') ?>"
>
    <section class="section">
        <div class="section__inner">
            <header class="ggz-page-hero ggz-page-hero--structured">
                <div class="ggz-page-hero__inner">
                    <div class="ggz-page-hero__copy section__header">
                        <?php if (trim($heroOverline) !== '') : ?>
                            <div class="section__overline"><?= esc($heroOverline) ?></div>
                        <?php endif; ?>
                        <h1 id="positions-program-heading" class="section__title"><?= esc($heroTitle) ?></h1>
                        <?php if (trim($heroLead) !== '') : ?>
                            <p class="section__lead"><?= nl2br(esc($heroLead)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="positions-program-page__trust" role="list">
                <span role="listitem">✓ <?= esc(lang('Positions.hero_trust_sourced')) ?></span>
                <span role="listitem">✓ <?= esc(lang('Positions.hero_trust_independent')) ?></span>
                <span role="listitem">✓ <?= esc(lang('Positions.hero_trust_budget')) ?></span>
                <span role="listitem">✓ <?= esc(lang('Positions.hero_trust_alternatives')) ?></span>
            </div>

            <div class="positions-program-page__stats" role="region" aria-label="<?= esc(lang('Positions.stats_region_aria'), 'attr') ?>">
                <div class="positions-program-page__stat">
                    <span class="positions-program-page__stat-value"><?= esc((string) $stats['published_count']) ?></span>
                    <span class="positions-program-page__stat-label"><?= esc(lang('Positions.stats_published')) ?></span>
                </div>
                <div class="positions-program-page__stat">
                    <span class="positions-program-page__stat-value"><?= esc((string) $stats['sectors_covered']) ?></span>
                    <span class="positions-program-page__stat-label"><?= esc(lang('Positions.stats_sectors')) ?></span>
                </div>
                <div class="positions-program-page__stat">
                    <span class="positions-program-page__stat-value"><?= esc((string) $stats['types_count']) ?></span>
                    <span class="positions-program-page__stat-label"><?= esc(lang('Positions.stats_types')) ?></span>
                </div>
                <div class="positions-program-page__stat">
                    <span class="positions-program-page__stat-value">100 %</span>
                    <span class="positions-program-page__stat-label"><?= esc(lang('Positions.stats_sourced')) ?></span>
                </div>
            </div>

            <p class="positions-program-page__ajax-error js-positions-ajax-error" role="alert" hidden></p>

            <div class="positions-program-page__filters">
                <?php if ($sectorFilterPills !== []) : ?>
                    <span class="positions-program-page__filters-label"><?= esc(lang('Positions.filter_sector')) ?></span>
                    <?php foreach ($sectorFilterPills as $secCode => $pillLabel) :
                        $c         = strtolower((string) $secCode);
                        $isActive  = in_array($c, $filterSectors, true);
                        $longLabel = $sectorOptions[$c] ?? $pillLabel;
                        ?>
                        <button
                            type="button"
                            class="positions-program-page__pill<?= $isActive ? ' positions-program-page__pill--active' : '' ?> js-positions-filter-pill"
                            data-filter-kind="sector"
                            data-filter-value="<?= esc($c, 'attr') ?>"
                            title="<?= esc($longLabel, 'attr') ?>"
                        ><?= esc($pillLabel) ?></button>
                    <?php endforeach; ?>
                <?php endif; ?>

                <span class="positions-program-page__filters-label positions-program-page__filters-label--gap"><?= esc(lang('Positions.filter_type')) ?></span>
                <?php
                $typeFilterLabels = position_type_filter_labels($loc);
                foreach ($typeFilterLabels as $code => $label) :
                    $isActive = in_array($code, $filterTypes, true);
                    $tip      = $typeTips[$code] ?? '';
                    ?>
                    <button
                        type="button"
                        class="positions-program-page__pill<?= $isActive ? ' positions-program-page__pill--active' : '' ?> js-positions-filter-pill"
                        data-filter-kind="type"
                        data-filter-value="<?= esc($code, 'attr') ?>"
                        <?php if ($tip !== '') : ?>title="<?= esc($tip, 'attr') ?>"<?php endif; ?>
                    ><?= esc($label) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="positions-program-page__grid-block">
                <div class="positions-program-page__grid-head">
                    <p class="positions-program-page__grid-meta js-positions-grid-meta">
                        <?= view('front/positions/partials/grid_meta', [
                            'shownCount'       => $shownCount,
                            'filtersActive'    => $filtersActive,
                            'positionsListUrl' => $positionsListUrl,
                        ]) ?>
                    </p>
                </div>

                <div class="js-positions-grid-inner">
                    <?= view('front/positions/partials/cards_timeline', [
                        'positions'          => $positions,
                        'sectorOptions'      => $sectorOptions,
                        'sectorFilterPills'  => $sectorFilterPills,
                        'typeLabels'         => $typeLabels,
                    ]) ?>
                </div>
            </div>
        </div>
    </section>
</article>
