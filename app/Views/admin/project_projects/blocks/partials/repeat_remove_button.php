<?php

declare(strict_types=1);

/** @var string $title Texte du tooltip et aria-label */
$title = (string) ($title ?? lang('Admin.block_remove_line'));
/** @var string $extraClasses Classes Bootstrap additionnelles */
$extraClasses = trim((string) ($extraClasses ?? ''));
$classes = trim('btn btn-sm btn-outline-danger pp-repeat-remove pp-row-remove-btn px-2 py-1 flex-shrink-0 align-middle ' . $extraClasses);
?>
<button type="button" class="<?= esc($classes, 'attr') ?>" style="min-width:2.5rem;line-height:1.25" data-pp-action="remove-row" title="<?= esc($title) ?>" aria-label="<?= esc($title) ?>"><span aria-hidden="true">&times;</span></button>
