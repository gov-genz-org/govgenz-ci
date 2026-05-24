<?php

declare(strict_types=1);

use App\Libraries\PublicNav;
use App\Libraries\SiteContext;

/**
 * @var list<array{href: string, label: string, match_key: string, css_class: string}> $navLinks
 * @var string $navActive
 */
$seg1 = SiteContext::publicSegment(1);
$seg2 = SiteContext::publicSegment(2);

foreach ($navLinks as $link) :
    $active = PublicNav::isActive($link['match_key'], $navActive, $seg1, $seg2);
    $parts  = array_values(array_filter([
        $active ? 'is-active' : null,
        ($link['css_class'] ?? '') !== '' ? trim((string) $link['css_class']) : null,
    ], static fn ($x) => $x !== null && $x !== ''));
    $linkClass = $parts !== [] ? implode(' ', $parts) : '';
    ?>
    <a href="<?= esc($link['href'], 'attr') ?>"<?= $linkClass !== '' ? ' class="' . esc($linkClass, 'attr') . '"' : '' ?>><?= esc($link['label']) ?></a>
<?php endforeach; ?>
