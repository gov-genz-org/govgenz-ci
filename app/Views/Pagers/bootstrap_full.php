<?php

declare(strict_types=1);

use CodeIgniter\Pager\PagerRenderer;

$pager->setSurroundCount(2);
?>
<nav aria-label="Pagination">
    <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-end">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>">«</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>">Précédent</a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item<?= $link['active'] ? ' active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>">Suivant</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>">»</a>
            </li>
        <?php endif ?>
    </ul>
</nav>
