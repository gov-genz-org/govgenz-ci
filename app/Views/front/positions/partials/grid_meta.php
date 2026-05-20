<?php

declare(strict_types=1);

/** @var int $shownCount */
/** @var bool $filtersActive */

$shownLabel = $shownCount > 1
    ? lang('Positions.grid_shown_many', ['count' => (string) $shownCount])
    : lang('Positions.grid_shown_one', ['count' => (string) $shownCount]);
?>
<?= esc($shownLabel) ?>
<?php if ($filtersActive) : ?>
    <?= esc(lang('Positions.grid_meta_filtered')) ?>
    — <button type="button" class="positions-program-page__pill positions-program-page__pill--active positions-program-page__pill--sm js-positions-reset-filters"><?= esc(lang('Positions.grid_reset_filters')) ?></button>
<?php endif; ?>
