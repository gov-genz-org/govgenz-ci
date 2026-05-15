<?php

declare(strict_types=1);

/** @var int $shownCount */
/** @var bool $filtersActive */
/** @var string $projectsListUrl */

$shownLabel = $shownCount > 1
    ? lang('Projects.grid_shown_many', ['count' => (string) $shownCount])
    : lang('Projects.grid_shown_one', ['count' => (string) $shownCount]);
?>
<?= esc($shownLabel) ?>
<?php if ($filtersActive) : ?>
    — <button type="button" class="projects-program-page__pill projects-program-page__pill--active projects-program-page__pill--sm js-projects-reset-filters"><?= esc(lang('Projects.grid_reset_filters')) ?></button>
<?php endif; ?>
