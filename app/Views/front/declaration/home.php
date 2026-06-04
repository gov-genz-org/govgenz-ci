<?php

declare(strict_types=1);

/** @var string $heroOverline */
/** @var string $heroTitle */
/** @var string $heroLead */
/** @var array{declarations: int, pledges: int, sectors: int, sourced_percent: string} $stats */
/** @var list<array<string, mixed>> $declarationItems */
/** @var list<array<string, mixed>> $partnershipItems */
/** @var string $staticBodyBefore */
/** @var string $staticBodyAfter */
?>
<article
    class="wysiwyg ggz-cms-fullwidth ggz-shell-wysiwyg declaration-program-page"
    aria-labelledby="declaration-program-heading"
>
    <section class="section">
        <div class="section__inner">
            <header class="ggz-page-hero ggz-page-hero--structured">
                <div class="ggz-page-hero__inner">
                    <div class="ggz-page-hero__copy section__header">
                        <?php if (trim($heroOverline) !== '') : ?>
                            <div class="section__overline"><?= esc($heroOverline) ?></div>
                        <?php endif; ?>
                        <h1 id="declaration-program-heading" class="section__title">
                            <?php
                            $titleRaw = trim($heroTitle);
                            $commaPos = strpos($titleRaw, ',');
                            if ($commaPos !== false) {
                                echo esc(trim(substr($titleRaw, 0, $commaPos)));
                                echo ',<br><span class="declaration-program-page__hero-accent">';
                                echo esc(trim(substr($titleRaw, $commaPos + 1)));
                                echo '</span>';
                            } else {
                                echo esc($titleRaw);
                            }
                            ?>
                        </h1>
                        <?php if (trim($heroLead) !== '') : ?>
                            <p class="section__lead"><?= nl2br(esc($heroLead)) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="declaration-program-page__trust" role="list">
                <span role="listitem">✓ <?= esc(lang('Declaration.hero_trust_official')) ?></span>
                <span role="listitem">✓ <?= esc(lang('Declaration.hero_trust_partnerships')) ?></span>
                <span role="listitem">✓ <?= esc(lang('Declaration.hero_trust_advocacy')) ?></span>
                <span role="listitem">✓ <?= esc(lang('Declaration.hero_trust_sourced')) ?></span>
            </div>

            <div class="declaration-program-page__stats" role="region" aria-label="<?= esc(lang('Declaration.stats_region_aria'), 'attr') ?>">
                <div class="declaration-program-page__stat">
                    <span class="declaration-program-page__stat-value"><?= esc((string) $stats['declarations']) ?></span>
                    <span class="declaration-program-page__stat-label"><?= esc(lang('Declaration.stat_declarations')) ?></span>
                </div>
                <div class="declaration-program-page__stat">
                    <span class="declaration-program-page__stat-value"><?= esc((string) $stats['pledges']) ?></span>
                    <span class="declaration-program-page__stat-label"><?= esc(lang('Declaration.stat_pledges')) ?></span>
                </div>
                <div class="declaration-program-page__stat">
                    <span class="declaration-program-page__stat-value"><?= esc((string) $stats['sectors']) ?></span>
                    <span class="declaration-program-page__stat-label"><?= esc(lang('Declaration.stat_sectors')) ?></span>
                </div>
                <div class="declaration-program-page__stat">
                    <span class="declaration-program-page__stat-value"><?= esc($stats['sourced_percent']) ?></span>
                    <span class="declaration-program-page__stat-label"><?= esc(lang('Declaration.stat_sourced')) ?></span>
                </div>
            </div>

            <div id="declarations" class="declaration-program-page__block declaration-program-page__block--declarations">
                <header class="declaration-program-page__section-head">
                    <p class="declaration-program-page__section-label"><?= esc(lang('Declaration.section_declarations_label')) ?></p>
                    <h2 class="declaration-program-page__section-title"><?= esc(lang('Declaration.section_declarations_title')) ?></h2>
                    <p class="declaration-program-page__section-lead"><?= esc(lang('Declaration.section_declarations_lead')) ?></p>
                </header>
                <?php if ($declarationItems === []) : ?>
                    <p class="declaration-program-page__empty"><?= esc(lang('Declaration.empty_declarations')) ?></p>
                <?php else : ?>
                    <?= view('front/declaration/partials/decl_cards', ['items' => $declarationItems]) ?>
                <?php endif; ?>
            </div>

            <?php if (trim($staticBodyBefore) !== '') : ?>
                <div class="declaration-program-page__static">
                    <?= $staticBodyBefore ?>
                </div>
            <?php endif; ?>

            <div class="declaration-program-page__block declaration-program-page__block--partnerships">
                <header class="declaration-program-page__section-head">
                    <p class="declaration-program-page__section-label"><?= esc(lang('Declaration.section_partnerships_label')) ?></p>
                    <h2 class="declaration-program-page__section-title"><?= esc(lang('Declaration.section_partnerships_title')) ?></h2>
                    <p class="declaration-program-page__section-lead"><?= esc(lang('Declaration.section_partnerships_lead')) ?></p>
                </header>
                <?php if ($partnershipItems === []) : ?>
                    <p class="declaration-program-page__empty"><?= esc(lang('Declaration.empty_partnerships')) ?></p>
                <?php else : ?>
                    <?= view('front/declaration/partials/decl_cards', ['items' => $partnershipItems]) ?>
                <?php endif; ?>
            </div>

            <?php if (trim($staticBodyAfter) !== '') : ?>
                <div class="declaration-program-page__static">
                    <?= $staticBodyAfter ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</article>
