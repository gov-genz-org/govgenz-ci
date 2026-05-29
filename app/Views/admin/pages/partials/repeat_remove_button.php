<?php

declare(strict_types=1);

/** @var string $title */
$title = (string) ($title ?? lang('Admin.block_remove_line'));
?>
<button type="button" class="btn btn-sm btn-outline-danger cms-repeat-remove px-2 py-1 flex-shrink-0" style="min-width:2.5rem;line-height:1.25" title="<?= esc($title, 'attr') ?>" aria-label="<?= esc($title, 'attr') ?>"><span aria-hidden="true">&times;</span></button>
