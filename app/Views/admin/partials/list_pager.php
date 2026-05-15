<?php

declare(strict_types=1);

/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $resultLabel */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted admin-list-pager">
    <div><?= (int) $pager->getTotal('default') ?> <?= esc($resultLabel ?? 'résultat(s)') ?></div>
    <?php if ($pager->getPageCount('default') > 1) : ?>
        <?= $pager->links('default', 'bootstrap_full') ?>
    <?php endif; ?>
</div>
