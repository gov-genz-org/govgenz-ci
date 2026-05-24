<?php

declare(strict_types=1);

use App\Libraries\SiteContext;

helper(['project', 'language']);

/** @var list<string> $segments */
/** @var list<array<string, mixed>> $projects */
/** @var array<string, string> $sectorOptions codes en minuscules => libellé long (cartes, infobulle filtres) */
/** @var array<string, string> $sectorFilterPills codes en minuscules => libellé court (pastilles filtres) */
/** @var array<string, string> $statusLabels */
/** @var list<string> $filterStatuses */
/** @var list<string> $filterSectors */
/** @var array{active_projects: int, volunteers_sum: int, sectors_covered: int, budget_total_display: string} $stats */
/** @var string $projectsListUrl */
/** @var string $filterPostUrl */
/** @var string $csrfTokenName */
/** @var string $csrfHash */
/** @var string $heroOverline */
/** @var string $heroTitle */
/** @var string $heroLead */

$loc = SiteContext::locale();
$membersDisplay = $stats['volunteers_sum'] > 0
    ? ($loc === 'en'
        ? number_format($stats['volunteers_sum'], 0, '.', ',')
        : number_format($stats['volunteers_sum'], 0, ',', ' ')) . '+'
    : lang('Projects.stats_value_emdash');

$filtersActive = $filterStatuses !== [] || $filterSectors !== [];
$shownCount    = count($projects);
?>
<article
    class="wysiwyg ggz-cms-fullwidth ggz-shell-wysiwyg projects-program-page js-projects-program-root"
    aria-labelledby="projects-program-heading"
    data-filter-endpoint="<?= esc($filterPostUrl, 'attr') ?>"
    data-csrf-name="<?= esc($csrfTokenName, 'attr') ?>"
    data-csrf-hash="<?= esc($csrfHash, 'attr') ?>"
    data-csrf-header="<?= esc(csrf_header(), 'attr') ?>"
    data-ajax-error="<?= esc(lang('Projects.ajax_error'), 'attr') ?>"
    data-ajax-csrf-reload="<?= esc(lang('Projects.ajax_csrf_reload'), 'attr') ?>"
>
    <section class="section">
        <div class="section__inner">
            <header class="ggz-page-hero ggz-page-hero--structured">
                <div class="ggz-page-hero__inner">
                    <div class="ggz-page-hero__copy section__header">
                        <?php if (trim($heroOverline) !== '') : ?>
                            <div class="section__overline"><?= esc($heroOverline) ?></div>
                        <?php endif; ?>
                        <h1 id="projects-program-heading" class="section__title"><?= esc($heroTitle) ?></h1>
                        <?php if (trim($heroLead) !== '') : ?>
                            <p class="section__lead"><?= nl2br(esc($heroLead)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="projects-program-page__stats" role="region" aria-label="<?= esc(lang('Projects.stats_region_aria'), 'attr') ?>">
                <div class="projects-program-page__stat">
                    <span class="projects-program-page__stat-value"><?= esc((string) $stats['active_projects']) ?></span>
                    <span class="projects-program-page__stat-label"><?= esc(lang('Projects.stats_active_projects')) ?></span>
                </div>
                <div class="projects-program-page__stat">
                    <span class="projects-program-page__stat-value"><?= esc($membersDisplay) ?></span>
                    <span class="projects-program-page__stat-label"><?= esc(lang('Projects.stats_members_engaged')) ?></span>
                </div>
                <div class="projects-program-page__stat">
                    <span class="projects-program-page__stat-value"><?= esc((string) $stats['sectors_covered']) ?></span>
                    <span class="projects-program-page__stat-label"><?= esc(lang('Projects.stats_sectors_covered')) ?></span>
                </div>
                <div class="projects-program-page__stat">
                    <span class="projects-program-page__stat-value"><?= esc($stats['budget_total_display']) ?></span>
                    <span class="projects-program-page__stat-label"><?= esc(lang('Projects.stats_budget_total')) ?></span>
                </div>
            </div>

            <p class="projects-program-page__ajax-error js-projects-ajax-error" role="alert" hidden></p>

            <div class="projects-program-page__filters">
                <span class="projects-program-page__filters-label"><?= esc(lang('Projects.filter_status')) ?></span>
                <?php foreach ($statusLabels as $code => $label) :
                    $isActive = in_array($code, $filterStatuses, true);
                    ?>
                    <button
                        type="button"
                        class="projects-program-page__pill<?= $isActive ? ' projects-program-page__pill--active' : '' ?> js-projects-filter-pill"
                        data-filter-kind="status"
                        data-filter-value="<?= esc($code, 'attr') ?>"
                    ><?= esc($label) ?></button>
                <?php endforeach; ?>

                <?php if ($sectorFilterPills !== []) : ?>
                    <span class="projects-program-page__filters-label projects-program-page__filters-label--gap"><?= esc(lang('Projects.filter_sector')) ?></span>
                    <?php foreach ($sectorFilterPills as $secCode => $pillLabel) :
                        $c         = strtolower((string) $secCode);
                        $isActive  = in_array($c, $filterSectors, true);
                        $longLabel = $sectorOptions[$c] ?? $pillLabel;
                        ?>
                        <button
                            type="button"
                            class="projects-program-page__pill<?= $isActive ? ' projects-program-page__pill--active' : '' ?> js-projects-filter-pill"
                            data-filter-kind="sector"
                            data-filter-value="<?= esc($c, 'attr') ?>"
                            title="<?= esc($longLabel, 'attr') ?>"
                        ><?= esc($pillLabel) ?></button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="projects-program-page__grid-block">
                <div class="projects-program-page__grid-head">
                    <p class="projects-program-page__grid-meta js-projects-grid-meta">
                        <?= view('front/projects/partials/grid_meta', [
                            'shownCount'      => $shownCount,
                            'filtersActive'   => $filtersActive,
                            'projectsListUrl' => $projectsListUrl,
                        ]) ?>
                    </p>
                </div>

                <div class="js-projects-grid-inner">
                    <?= view('front/projects/partials/cards_grid', [
                        'projects'           => $projects,
                        'sectorOptions'      => $sectorOptions,
                        'sectorFilterPills'  => $sectorFilterPills,
                        'statusLabels'       => $statusLabels,
                    ]) ?>
                </div>
            </div>
        </div>
    </section>
</article>
