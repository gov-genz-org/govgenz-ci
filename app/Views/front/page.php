<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $page
 */
helper(['cms']);

$structuredHero    = cms_page_structured_hero_active($page);
$suppressOuterHero = $structuredHero || cms_page_suppress_outer_hero((string) ($page['slug'] ?? ''));
$articleClass      = $suppressOuterHero ? 'wysiwyg ggz-cms-fullwidth' : 'wysiwyg ggz-page-prose';

$tgroup = strtolower(trim((string) ($page['translation_group'] ?? '')));
$slug   = strtolower(trim((string) ($page['slug'] ?? '')));
if ($tgroup === 'projects-program-list' || in_array($slug, ['projets-programme', 'projects-program'], true)) {
    $articleClass .= ' ggz-cms-page--projects-program-note';
}
if ($slug === 'mentions-legales') {
    $articleClass .= ' ggz-cms-page--legal';
}
?>
<?php if ($structuredHero) : ?>
    <?= cms_render_structured_page_hero($page) ?>
<?php elseif (! cms_page_suppress_outer_hero((string) ($page['slug'] ?? ''))) : ?>
    <header class="ggz-page-hero ggz-page-hero--compact">
        <h1><?= esc($page['title']) ?></h1>
    </header>
<?php endif; ?>
<article class="<?= esc($articleClass, 'attr') ?>"><?= cms_render_page_body($page) ?></article>
