<?php

declare(strict_types=1);

helper(['project', 'language']);

/** @var array<string, mixed> $project */
/** @var string $statusLabel */
/** @var string $statusBadge */
/** @var string $launchedDisplay */
/** @var list<array{slug: string, title: string}> $relatedProjects */
/** @var string $projectsListUrl */
/** @var string $shareUrl */
/** @var array<string, string>|null $currencyLines */
/** @var string $currencyRatesHeader */

$title    = (string) ($project['title'] ?? '');
$budget   = trim((string) ($project['budget_display'] ?? ''));
$geoDisplay = project_geography_front_display($project);
$vol      = (int) ($project['volunteers_count'] ?? 0);
$duration = (int) ($project['duration_months'] ?? 0);
$mailSub  = rawurlencode($title);
?>
<aside class="project-sidebar" aria-label="<?= esc(lang('Projects.show_sidebar_aria'), 'attr') ?>">

    <div class="widget">
        <div class="widget-title"><?= esc(lang('Projects.show_widget_key_info')) ?></div>
        <div class="widget-row">
            <span><?= esc(lang('Projects.show_widget_status')) ?></span>
            <span><span class="status <?= esc($statusBadge, 'attr') ?>"><?= esc($statusLabel) ?></span></span>
        </div>
        <?php if ($launchedDisplay !== '') : ?>
            <div class="widget-row">
                <span><?= esc(lang('Projects.show_widget_launched')) ?></span>
                <span><?= esc($launchedDisplay) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($duration > 0) : ?>
            <div class="widget-row">
                <span><?= esc(lang('Projects.show_widget_duration')) ?></span>
                <span><?= esc((string) $duration) ?> <?= esc(lang('Projects.show_months')) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($budget !== '') : ?>
            <div class="widget-row">
                <span><?= esc(lang('Projects.show_budget')) ?></span>
                <span><?= esc($budget) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($vol > 0) : ?>
            <div class="widget-row">
                <span><?= esc(lang('Projects.show_volunteers')) ?></span>
                <span><?= esc((string) $vol) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($geoDisplay['html'] !== '') : ?>
            <div class="widget-row">
                <span><?= esc(lang('Projects.show_widget_zones')) ?></span>
                <span><?= $geoDisplay['html'] ?></span>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($budget !== '') : ?>
        <div class="widget">
            <div class="widget-title"><?= esc(lang('Projects.show_widget_financing')) ?></div>
            <div class="widget-row">
                <span><?= esc(lang('Projects.show_widget_total_budget')) ?></span>
                <span><?= esc($budget) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="widget">
        <div class="widget-title"><?= esc(lang('Projects.show_widget_get_involved')) ?></div>
        <p class="projects-program-show__widget-lead"><?= esc(lang('Projects.show_widget_get_involved_lead')) ?></p>
        <div class="widget-cta">
            <a href="mailto:projets@govgenz.org?subject=<?= esc('Volontaire — ' . $mailSub, 'attr') ?>" class="projects-program-show__btn projects-program-show__btn--red"><?= esc(lang('Projects.show_cta_volunteer')) ?></a>
            <a href="mailto:partnerships@govgenz.org?subject=<?= esc('Financement — ' . $mailSub, 'attr') ?>" class="projects-program-show__btn projects-program-show__btn--teal"><?= esc(lang('Projects.show_cta_fund')) ?></a>
            <a href="mailto:projets@govgenz.org?subject=<?= esc('Concept note — ' . $mailSub, 'attr') ?>" class="projects-program-show__btn projects-program-show__btn--ghost"><?= esc(lang('Projects.show_cta_concept')) ?></a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-title"><?= esc(lang('Projects.show_widget_share')) ?></div>
        <p class="projects-program-show__widget-lead"><?= esc(lang('Projects.show_widget_share_lead')) ?></p>
        <code class="projects-program-show__share-url"><?= esc($shareUrl) ?></code>
    </div>

    <?php if ($currencyLines !== null && $currencyLines !== []) : ?>
        <div class="widget">
            <div class="widget-title"><?= esc(lang('Projects.show_widget_currency')) ?></div>
            <div class="currency-rates-header"><?= $currencyRatesHeader ?? '' ?></div>
            <span class="currency-sep"><?= esc(lang('Projects.show_currency_budget_line', ['budget' => $budget])) ?></span>
            <?php foreach ($currencyLines as $flagLabel => $amount) : ?>
                <div class="currency-line">
                    <span><?= esc($flagLabel) ?></span>
                    <span><?= esc($amount) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="widget">
        <div class="widget-title"><?= esc(lang('Projects.show_widget_see_also')) ?></div>
        <div class="projects-program-show__see-also">
            <?php foreach ($relatedProjects as $rel) : ?>
                <a href="<?= esc(project_public_url($rel['slug']), 'attr') ?>"><?= esc($rel['title']) ?></a>
            <?php endforeach; ?>
            <a href="<?= esc($projectsListUrl, 'attr') ?>" class="is-muted"><?= esc(lang('Projects.show_back_to_list')) ?></a>
        </div>
    </div>

</aside>
