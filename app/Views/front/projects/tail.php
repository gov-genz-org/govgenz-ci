<?php

declare(strict_types=1);

/** @var string $path */
/** @var list<string> $segments */
?>
<section class="container py-5">
    <h1 class="h2 mb-3">Projets</h1>
    <p class="text-muted small mb-2">Chemin : <code><?= esc($path) ?></code></p>
    <p class="text-muted small mb-0">Segments internes : <?= esc(implode(' / ', $segments)) ?></p>
</section>
