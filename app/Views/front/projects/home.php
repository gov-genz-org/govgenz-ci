<?php

declare(strict_types=1);

/** @var list<string> $segments */
?>
<section class="container py-5">
    <h1 class="h2 mb-3">Projets</h1>
    <p class="text-muted mb-0">
        Espace module <strong>projects</strong> (préfixe <code>/projects</code> sur un seul domaine).
        Active <code>app.projectsUsePathPrefix</code> dans <code>.env</code> pour ce mode ; migration sous-domaine : désactiver le préfixe et utiliser <code>app.projectsHost</code> / <code>app.projectsBaseURL</code>.
    </p>
    <?php if ($segments !== []) : ?>
        <p class="small mt-3 mb-0"><strong>Segments internes</strong> : <?= esc(implode(' / ', $segments)) ?></p>
    <?php endif; ?>
</section>
