<?php

declare(strict_types=1);

helper(['project', 'language']);

/** @var array<string, mixed> $project */
/** @var array<string, string> $sectorFilterPills */
/** @var string $statusLabel */
/** @var string $statusBadge */
/** @var string $launchedDisplay */
/** @var list<array{slug: string, title: string}> $relatedProjects */
/** @var string $projectsListUrl */
/** @var string $shareUrl */
/** @var array<string, string>|null $currencyLines */

$locale = \App\Libraries\SiteContext::locale();
$currencyRatesHeader = project_currency_rates_header_html($locale);

$title   = (string) ($project['title'] ?? '');
$excerpt = trim((string) ($project['excerpt'] ?? ''));
$budget  = trim((string) ($project['budget_display'] ?? ''));
$geoDisplay = project_geography_front_display($project);
$vol     = (int) ($project['volunteers_count'] ?? 0);
$duration = (int) ($project['duration_months'] ?? 0);
$prog    = $project['progress_percent'];
$progInt = is_numeric($prog) ? max(0, min(100, (int) $prog)) : null;

$sectorCodes = project_sector_codes_from_csv((string) ($project['sectors_csv'] ?? ''));

?>
<article class="projects-program-show" aria-labelledby="project-show-heading">


    <nav class="projects-program-show__breadcrumb" aria-label="<?= esc(lang('Projects.breadcrumb_aria'), 'attr') ?>">
        <a href="<?= esc($projectsListUrl, 'attr') ?>"><?= esc(lang('Projects.breadcrumb_list')) ?></a>
        <span aria-hidden="true">›</span>
        <span><?= esc($title) ?></span>
    </nav>

    <header class="project-hero">
        <div class="project-hero-inner">
            <?php if ($sectorCodes !== [] || $statusLabel !== '') : ?>
                <div class="tags">
                    <?php foreach (array_slice($sectorCodes, 0, 4) as $code) : ?>
                        <span class="tag"><?= esc($sectorFilterPills[$code] ?? $code) ?></span>
                    <?php endforeach; ?>
                    <?php if ($statusLabel !== '') : ?>
                        <span class="status <?= esc($statusBadge, 'attr') ?>"><?= esc($statusLabel) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <h1 id="project-show-heading" class="section__title"><?= esc($title) ?></h1>
            <div class="project-hero-meta">
                <?php if ($launchedDisplay !== '') : ?>
                    <span>📅 <?= esc(lang('Projects.show_hero_launched')) ?> <strong><?= esc($launchedDisplay) ?></strong></span>
                <?php endif; ?>
                <?php if ($vol > 0) : ?>
                    <span>👥 <strong><?= esc((string) $vol) ?></strong> <?= esc(lang('Projects.card_volunteers')) ?></span>
                <?php endif; ?>
                <?php if ($budget !== '') : ?>
                    <span>💰 <?= esc(lang('Projects.show_hero_budget')) ?> <strong><?= esc($budget) ?></strong></span>
                <?php endif; ?>
                <?php if ($geoDisplay['html'] !== '') : ?>
                    <span>📍 <strong><?= $geoDisplay['html'] ?></strong></span>
                <?php endif; ?>
                <?php if ($duration > 0) : ?>
                    <span>⏱ <?= esc(lang('Projects.show_hero_duration')) ?> <strong><?= esc((string) $duration) ?> <?= esc(lang('Projects.show_months')) ?></strong></span>
                <?php endif; ?>
            </div>
            <?php if ($excerpt !== '') : ?>
                <p class="project-hero-desc"><?= esc($excerpt) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($progInt !== null) : ?>
        <div class="projects-program-show__progress-band">
            <div class="projects-program-show__progress-inner">
                <div class="progress-wrap">
                    <div class="progress-label">
                        <span><?= esc(lang('Projects.show_progress_global')) ?></span>
                        <span><strong><?= esc((string) $progInt) ?> %</strong></span>
                    </div>
                    <div class="progress-bar" role="progressbar" aria-valuenow="<?= $progInt ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-fill" style="width:<?= $progInt ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="project-layout">
        <?= project_render_main_column($project) ?>
        <?= view('front/projects/partials/show_sidebar', [
            'project'          => $project,
            'statusLabel'      => $statusLabel,
            'statusBadge'      => $statusBadge,
            'launchedDisplay'  => $launchedDisplay,
            'relatedProjects'  => $relatedProjects,
            'projectsListUrl'  => $projectsListUrl,
            'shareUrl'        => $shareUrl,
            'shareQrImageUrl' => $shareQrImageUrl ?? '',
            'shareQrPageUrl'  => $shareQrPageUrl ?? '',
            'currencyLines'      => $currencyLines,
            'currencyRatesHeader' => $currencyRatesHeader,
        ]) ?>
    </div>

    <?= view('front/projects/partials/fund_modal', ['project' => $project]) ?>

</article>
