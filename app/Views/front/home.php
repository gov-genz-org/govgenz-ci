<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $page
 */
helper(['cms']);
?>

<?php if (cms_page_structured_hero_active($page)) : ?>
    <?= cms_render_structured_page_hero($page) ?>
<?php endif; ?>

<article class="wysiwyg ggz-home-wysiwyg"><?= cms_render_page_body($page) ?></article>
