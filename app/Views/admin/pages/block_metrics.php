<?php

declare(strict_types=1);

/** @var int|string $i */
/** @var array<string, mixed> $block */

echo view('admin/pages/block_stats_grid', [
    'i'     => $i,
    'block' => $block,
]);
