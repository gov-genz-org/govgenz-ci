<?php

declare(strict_types=1);

helper(['project', 'language', 'locale']);

/** @var array<string, mixed> $project */
/** @var string $statusLabel */
/** @var string $statusBadge */
/** @var string $launchedDisplay */
/** @var list<array{slug: string, title: string}> $relatedProjects */
/** @var string $projectsListUrl */
/** @var string $shareUrl */
/** @var string $shareQrImageUrl */
/** @var string $shareQrPageUrl */
/** @var array<string, string>|null $currencyLines */
/** @var string $currencyRatesHeader */

$title    = (string) ($project['title'] ?? '');
$budget   = trim((string) ($project['budget_display'] ?? ''));
$geoDisplay = project_geography_front_display($project);
$vol      = (int) ($project['volunteers_count'] ?? 0);
$duration = (int) ($project['duration_months'] ?? 0);
$mailSub  = rawurlencode($title);
$sectorCodes       = project_sector_codes_from_csv((string) ($project['sectors_csv'] ?? ''));
$joinVolunteerUrl  = public_join_url($sectorCodes);
$showFundBudget    = project_has_financial_funding($project);
$showFundMaterial  = project_has_material_needs($project);
$showFundCta       = $showFundBudget || $showFundMaterial;

$shareQrImageUrl = (string) ($shareQrImageUrl ?? '');
$shareQrPageUrl  = (string) ($shareQrPageUrl ?? '');
if ($shareQrImageUrl === '' || $shareQrPageUrl === '') {
    $slug = strtolower(trim((string) ($project['slug'] ?? '')));
    if ($slug !== '') {
        if ($shareQrImageUrl === '') {
            $shareQrImageUrl = project_share_qr_image_url($slug);
        }
        if ($shareQrPageUrl === '') {
            $shareQrPageUrl = project_share_qr_page_url($slug);
        }
    }
}
$shareSocialLinks = project_share_social_links($title, $shareUrl, $shareQrPageUrl);
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
            <a href="<?= esc($joinVolunteerUrl, 'attr') ?>" class="projects-program-show__btn projects-program-show__btn--red"><?= esc(lang('Projects.show_cta_volunteer')) ?></a>
            <?php if ($showFundCta) : ?>
                <button type="button" class="projects-program-show__btn projects-program-show__btn--teal" data-fund-modal-open><?= esc(lang('Projects.show_cta_fund')) ?></button>
            <?php endif; ?>
            <a href="mailto:projets@govgenz.org?subject=<?= esc('Concept note — ' . $mailSub, 'attr') ?>" class="projects-program-show__btn projects-program-show__btn--ghost"><?= esc(lang('Projects.show_cta_concept')) ?></a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-title"><?= esc(lang('Projects.show_widget_share')) ?></div>
        <div
            class="projects-program-show__share"
            data-project-share
            data-share-url="<?= esc($shareUrl, 'attr') ?>"
            data-share-title="<?= esc($title, 'attr') ?>"
            data-share-qr-image="<?= esc($shareQrImageUrl, 'attr') ?>"
            data-share-qr-page="<?= esc($shareQrPageUrl, 'attr') ?>"
            data-share-copied="<?= esc(lang('Projects.show_share_copied'), 'attr') ?>"
        >
            <div class="projects-program-show__share-url-head">
                <p class="projects-program-show__widget-lead"><?= esc(lang('Projects.show_widget_share_lead')) ?></p>
                <button
                    type="button"
                    class="projects-program-show__share-icon-btn"
                    data-project-share-copy
                    aria-label="<?= esc(lang('Projects.show_share_copy_aria'), 'attr') ?>"
                    title="<?= esc(lang('Projects.show_share_copy_aria'), 'attr') ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </button>
            </div>
            <code class="projects-program-show__share-url" data-project-share-url tabindex="0"><?= esc($shareUrl) ?></code>
            <div class="projects-program-show__share-qr-wrap">
                <p class="projects-program-show__share-qr-label"><?= esc(lang('Projects.show_share_qr_label')) ?></p>
                <?= view('front/projects/partials/share_qr_display', [
                    'qrImageUrl'  => $shareQrImageUrl,
                    'qrAlt'       => lang('Projects.show_share_qr_aria'),
                    'imgWidth'    => 140,
                    'imgHeight'   => 140,
                    'qrDataAttr'  => true,
                    'overlayLogo' => false,
                ]) ?>
            </div>
            <div class="projects-program-show__share-social" role="navigation" aria-label="<?= esc(lang('Projects.show_share_social_aria'), 'attr') ?>">
                <a class="projects-program-show__share-social-link" href="<?= esc($shareSocialLinks['facebook']['web'], 'attr') ?>" data-share-href-mobile="<?= esc($shareSocialLinks['facebook']['mobile'], 'attr') ?>" data-project-share-social="facebook" rel="noopener noreferrer" aria-label="<?= esc(lang('Projects.show_share_social_facebook'), 'attr') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                <a class="projects-program-show__share-social-link" href="<?= esc($shareSocialLinks['whatsapp']['web'], 'attr') ?>" data-share-href-app="<?= esc($shareSocialLinks['whatsapp']['app'], 'attr') ?>" data-share-href-android="<?= esc($shareSocialLinks['whatsapp']['android'] ?? '', 'attr') ?>" data-project-share-social="whatsapp" rel="noopener noreferrer" aria-label="<?= esc(lang('Projects.show_share_social_whatsapp'), 'attr') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                </a>
                <a class="projects-program-show__share-social-link" href="<?= esc($shareSocialLinks['linkedin']['web'], 'attr') ?>" data-share-href-mobile="<?= esc($shareSocialLinks['linkedin']['mobile'], 'attr') ?>" data-project-share-social="linkedin" rel="noopener noreferrer" aria-label="<?= esc(lang('Projects.show_share_social_linkedin'), 'attr') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.062 2.062 0 0 1 2.063-2.065 2.062 2.062 0 0 1 2.063 2.065 2.062 2.062 0 0 1-2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                </a>
                <a class="projects-program-show__share-social-link" href="<?= esc($shareSocialLinks['x']['web'], 'attr') ?>" data-share-href-mobile="<?= esc($shareSocialLinks['x']['mobile'], 'attr') ?>" data-project-share-social="x" rel="noopener noreferrer" aria-label="<?= esc(lang('Projects.show_share_social_x'), 'attr') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a class="projects-program-show__share-social-link" href="<?= esc($shareSocialLinks['email']['web'], 'attr') ?>" data-project-share-social="email" aria-label="<?= esc(lang('Projects.show_share_social_email'), 'attr') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </a>
            </div>
        </div>
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
