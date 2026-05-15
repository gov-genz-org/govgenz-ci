<?php

declare(strict_types=1);

helper('language');

/** @var string $path */
/** @var list<string> $segments */
?>
<section class="container py-5">
    <h1 class="h2 mb-3"><?= esc(lang('Projects.tail_stub_title')) ?></h1>
    <p class="text-muted small mb-2"><?= esc(lang('Projects.tail_path_label')) ?> <code><?= esc($path) ?></code></p>
    <p class="text-muted small mb-0"><?= esc(lang('Projects.tail_segments_label')) ?> <?= esc(implode(' / ', $segments)) ?></p>
</section>
