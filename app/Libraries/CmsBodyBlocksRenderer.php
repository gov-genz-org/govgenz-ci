<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Rendu HTML des blocs CMS structurés (JSON body_blocks).
 * Contenu éditorial : faire confiance au même niveau que body_html (admins).
 */
final class CmsBodyBlocksRenderer
{
    /**
     * @param list<array<string, mixed>> $blocks
     */
    public static function render(array $blocks): string
    {
        $out = '';
        foreach ($blocks as $b) {
            if (! is_array($b)) {
                continue;
            }
            $out .= match ((string) ($b['type'] ?? '')) {
                'section_text' => self::sectionText($b),
                'cards_grid' => self::cardsGrid($b),
                'metrics_section', 'stats_grid' => self::statsGrid($b),
                'organization_hub' => self::organizationHub($b),
                'contact_grid' => self::contactGrid($b),
                'cta_panel' => self::ctaPanel($b),
                'legal_prose' => self::legalProse($b),
                'sources' => self::sources($b),
                'sectors_grid' => self::sectorsGrid($b),
                'footer_columns' => self::footerColumns($b),
                'html' => (string) ($b['html'] ?? ''),
                default => '',
            };
        }

        return $out;
    }

    /**
     * @param string $text
     *
     * @return list<string>
     */
    private static function textLines(string $text): array
    {
        $parts = preg_split('/\R{2,}|\R/u', trim($text)) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $out[] = $part;
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function sectionText(array $b): string
    {
        $body = '';
        $paragraphs = $b['paragraphs'] ?? [];
        if (is_array($paragraphs)) {
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim((string) $paragraph);
                if ($paragraph !== '') {
                    $body .= '<p>' . esc($paragraph) . '</p>';
                }
            }
        }

        $bulletsHtml = '';
        $bullets = $b['bullets'] ?? [];
        if (is_array($bullets)) {
            foreach ($bullets as $bullet) {
                $bullet = trim((string) $bullet);
                if ($bullet !== '') {
                    $bulletsHtml .= '<li>' . esc($bullet) . '</li>';
                }
            }
        }
        if ($bulletsHtml !== '') {
            $body .= '<ul>' . $bulletsHtml . '</ul>';
        }

        $source = trim((string) ($b['source'] ?? ''));
        if ($source !== '') {
            $body .= '<p class="section__source"><span>' . esc($source) . '</span></p>';
        }

        if ($body === '') {
            return '';
        }

        return '<section class="section section--cms-text"><div class="section__inner">'
            . '<div class="ggz-editorial-prose">' . $body . '</div>'
            . '</div></section>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function cardsGrid(array $b): string
    {
        $variant = strtolower(trim((string) ($b['variant'] ?? 'simple_cards')));
        if (! in_array($variant, ['simple_cards', 'circle_cards', 'pillar_cards', 'tile_grid'], true)) {
            $variant = 'simple_cards';
        }

        $cards = $b['cards'] ?? [];
        $cardsHtml = '';
        if (is_array($cards)) {
            foreach (array_values($cards) as $idx => $card) {
                if (! is_array($card)) {
                    continue;
                }
                $cardsHtml .= match ($variant) {
                    'circle_cards' => self::circleCard($card, $idx),
                    'pillar_cards' => self::pillarCard($card, $idx),
                    'tile_grid' => self::tileCard($card, $idx),
                    default => self::simpleCard($card, $idx),
                };
            }
        }

        if ($cardsHtml === '') {
            return '';
        }

        $gridClass = match ($variant) {
            'circle_cards' => 'cercles',
            'pillar_cards' => 'adn',
            'tile_grid' => 'tile-grid',
            default => 'ggz-cards-row',
        };

        $sectionClass = match ($variant) {
            'pillar_cards' => 'section section--cms-cards section--adn',
            'circle_cards' => 'section section--cms-cards section--qui',
            default => 'section section--cms-cards',
        };

        $source = trim((string) ($b['source'] ?? ''));
        $sourceHtml = $source !== '' ? '<div class="section__source"><span>' . esc($source) . '</span></div>' : '';

        return '<section class="' . esc($sectionClass, 'attr') . '"><div class="section__inner">'
            . '<div class="' . esc($gridClass, 'attr') . '">' . $cardsHtml . '</div>'
            . $sourceHtml
            . '</div></section>';
    }

    /**
     * @param array<string, mixed> $card
     */
    private static function circleCard(array $card, int $idx): string
    {
        $title = trim((string) ($card['title'] ?? ''));
        $value = trim((string) ($card['value'] ?? ''));
        $unit = trim((string) ($card['unit'] ?? ''));
        $subtitle = trim((string) ($card['subtitle'] ?? ''));
        $description = trim((string) ($card['description'] ?? ''));
        $icon = trim((string) ($card['icon_url'] ?? ''));
        $mediaId = (int) ($card['media_id'] ?? 0);
        $mediaUrl = $mediaId > 0 && function_exists('cms_media_public_url') ? cms_media_public_url($mediaId) : null;
        $mediaId2 = (int) ($card['media_id_2'] ?? 0);
        $mediaUrl2 = $mediaId2 > 0 && function_exists('cms_media_public_url') ? cms_media_public_url($mediaId2) : null;
        if ($title === '' && $value === '' && $description === '') {
            return '';
        }

        $html = '<div class="cercle reveal" data-delay="' . ($idx * 100) . '">';
        $mediaItems = [];
        if ($mediaUrl !== null) {
            $mediaItems[] = [
                'url' => $mediaUrl,
                'alt' => trim((string) ($card['media_alt'] ?? '')),
            ];
        }
        if ($mediaUrl2 !== null) {
            $mediaItems[] = [
                'url' => $mediaUrl2,
                'alt' => trim((string) ($card['media_alt_2'] ?? '')),
            ];
        }
        if ($mediaItems !== []) {
            $iconClass = count($mediaItems) > 1 ? 'cercle__icon cercle__icon--media-pair' : 'cercle__icon';
            $html .= '<div class="' . esc($iconClass, 'attr') . '">';
            foreach ($mediaItems as $item) {
                $html .= '<img src="' . esc((string) $item['url'], 'attr') . '" alt="' . esc((string) $item['alt'], 'attr') . '" loading="lazy">';
            }
            $html .= '</div>';
        } elseif ($icon !== '') {
            $html .= '<div class="cercle__icon"><img src="' . esc($icon, 'attr') . '" alt="" loading="lazy"></div>';
        }
        if ($value !== '') {
            $numClass = $value === '∞' ? 'cercle__number cercle__number--text' : 'cercle__number';
            $html .= '<div class="' . esc($numClass, 'attr') . '" data-count="' . esc(str_replace(',', '.', $value), 'attr') . '">' . esc($value) . '</div>';
        }
        if ($unit !== '') {
            $html .= '<div class="cercle__unit">' . esc($unit) . '</div>';
        }
        if ($title !== '') {
            $html .= '<h3 class="cercle__title">' . esc($title) . '</h3>';
        }
        if ($subtitle !== '') {
            $html .= '<p class="cercle__sub">' . esc($subtitle) . '</p>';
        }
        if ($description !== '') {
            $html .= '<p class="cercle__desc">' . esc($description) . '</p>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $card
     */
    private static function pillarCard(array $card, int $idx): string
    {
        $title = trim((string) ($card['title'] ?? ''));
        $eyebrow = trim((string) ($card['eyebrow'] ?? ''));
        $value = trim((string) ($card['value'] ?? ''));
        $bullets = $card['bullets'] ?? [];
        if ($title === '' && $eyebrow === '' && ! is_array($bullets)) {
            return '';
        }

        $lis = '';
        if (is_array($bullets)) {
            foreach ($bullets as $line) {
                $line = trim((string) $line);
                if ($line !== '') {
                    $lis .= '<li>' . esc($line) . '</li>';
                }
            }
        }

        $num = $value !== '' ? $value : str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
        $html = '<article class="adn-card adn-card--' . (($idx % 4) + 1) . ' reveal" data-delay="' . ($idx * 100) . '">';
        $html .= '<div class="adn-card__num">' . esc($num) . '</div>';
        if ($eyebrow !== '') {
            $html .= '<div class="adn-card__overline">' . esc($eyebrow) . '</div>';
        }
        if ($title !== '') {
            $html .= '<h3 class="adn-card__title">' . esc($title) . '</h3>';
        }
        if ($lis !== '') {
            $html .= '<ul class="adn-card__list">' . $lis . '</ul>';
        }
        $html .= '</article>';

        return $html;
    }

    /**
     * @param array<string, mixed> $card
     */
    private static function tileCard(array $card, int $idx): string
    {
        $title = trim((string) ($card['title'] ?? ''));
        $subtitle = trim((string) ($card['subtitle'] ?? ''));
        $href = trim((string) ($card['href'] ?? ''));
        if ($title === '' && $subtitle === '') {
            return '';
        }
        $tag = $href !== '' ? 'a' : 'div';
        $hrefAttr = $href !== '' ? ' href="' . esc($href, 'attr') . '"' : '';

        return '<' . $tag . $hrefAttr . ' class="tile reveal" data-delay="' . ($idx * 40) . '">'
            . '<div class="tile__name">' . esc($title) . '</div>'
            . ($subtitle !== '' ? '<div class="tile__sub">' . esc($subtitle) . '</div>' : '')
            . ($href !== '' ? '<div class="tile__mail">' . esc(preg_replace('/^mailto:/', '', $href) ?? $href) . '</div>' : '')
            . '</' . $tag . '>';
    }

    /**
     * Cartes génériques (.card) — distinctes des piliers ADN (.adn-card).
     *
     * @param array<string, mixed> $card
     */
    private static function simpleCard(array $card, int $idx): string
    {
        $title = trim((string) ($card['title'] ?? ''));
        $eyebrow = trim((string) ($card['eyebrow'] ?? ''));
        $subtitle = trim((string) ($card['subtitle'] ?? ''));
        $description = trim((string) ($card['description'] ?? ''));
        $href = trim((string) ($card['href'] ?? ''));
        $bullets = $card['bullets'] ?? [];

        $lis = '';
        if (is_array($bullets)) {
            foreach ($bullets as $line) {
                $line = trim((string) $line);
                if ($line !== '') {
                    $lis .= '<li>' . esc($line) . '</li>';
                }
            }
        }

        if ($title === '' && $description === '' && $eyebrow === '' && $subtitle === '' && $lis === '') {
            return '';
        }

        $tag = $href !== '' ? 'a' : 'div';
        $hrefAttr = $href !== '' ? ' href="' . esc($href, 'attr') . '"' : '';

        $html = '<' . $tag . $hrefAttr . ' class="card">';
        if ($eyebrow !== '') {
            $html .= '<p class="ggz-eyebrow">' . esc($eyebrow) . '</p>';
        }
        if ($title !== '') {
            $html .= '<h3>' . esc($title) . '</h3>';
        }
        if ($subtitle !== '') {
            $html .= '<p class="muted">' . esc($subtitle) . '</p>';
        }
        if ($description !== '') {
            $html .= '<p>' . esc($description) . '</p>';
        }
        if ($lis !== '') {
            $html .= '<ul>' . $lis . '</ul>';
        }
        $html .= '</' . $tag . '>';

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function statsGrid(array $b): string
    {
        $title    = trim((string) ($b['title'] ?? ''));
        $footnote = trim((string) ($b['footnote'] ?? ''));

        $metricsHtml = '';
        $stats = $b['stats'] ?? ($b['metrics'] ?? []);
        if (is_array($stats)) {
            foreach (array_values($stats) as $idx => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $v = trim((string) ($row['value'] ?? ''));
                $suffix = trim((string) ($row['suffix'] ?? ''));
                $l = trim((string) ($row['label'] ?? ''));
                if ($v === '' && $suffix === '' && $l === '') {
                    continue;
                }
                $metricsHtml .= '<div class="stat reveal" data-delay="' . ($idx * 100) . '">';
                $metricsHtml .= '<div class="stat__num"><span data-count="' . esc(str_replace(',', '.', $v), 'attr') . '">' . esc($v) . '</span>';
                if ($suffix !== '') {
                    $metricsHtml .= '<span class="stat__suffix">' . esc($suffix) . '</span>';
                }
                $metricsHtml .= '</div>';
                if ($l !== '') {
                    $metricsHtml .= '<div class="stat__label">' . esc($l) . '</div>';
                }
                $metricsHtml .= '</div>';
            }
        }

        if ($metricsHtml === '') {
            return '';
        }

        $html = '<section class="section section--etude"><div class="section__inner">';
        if ($metricsHtml !== '') {
            $html .= '<div class="stats">' . $metricsHtml . '</div>';
        }
        $actionsHtml = '';
        $actions = $b['actions'] ?? [];
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (! is_array($action)) {
                    continue;
                }
                $label = trim((string) ($action['label'] ?? ''));
                $href = trim((string) ($action['href'] ?? ''));
                if ($label === '' || $href === '') {
                    continue;
                }
                $variant = strtolower(trim((string) ($action['variant'] ?? 'secondary')));
                $class = $variant === 'primary' ? 'btn btn--primary' : 'btn btn--secondary';
                $actionsHtml .= '<a href="' . esc($href, 'attr') . '" class="' . esc($class, 'attr') . '">' . esc($label) . '</a>';
            }
        }
        if ($actionsHtml !== '') {
            $html .= '<div class="section__btn-row">' . $actionsHtml . '</div>';
        }
        if ($footnote !== '') {
            $html .= '<div class="section__source"><span>' . esc($footnote) . '</span></div>';
        }
        $html .= '</div></section>';

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function organizationHub(array $b): string
    {
        $itemsHtml = '';
        $items = $b['items'] ?? [];
        if (is_array($items)) {
            foreach (array_values($items) as $idx => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $name = trim((string) ($item['name'] ?? ''));
                $subtitle = trim((string) ($item['subtitle'] ?? ''));
                $href = trim((string) ($item['href'] ?? ''));
                if ($name === '' && $subtitle === '' && $href === '') {
                    continue;
                }
                $tag = $href !== '' ? 'a' : 'div';
                $hrefAttr = $href !== '' ? ' href="' . esc($href, 'attr') . '"' : '';
                $itemsHtml .= '<' . $tag . $hrefAttr . ' class="fn-card reveal" data-delay="' . ($idx * 50) . '">';
                $itemsHtml .= '<div class="fn-card__name">' . esc($name) . '</div>';
                if ($subtitle !== '') {
                    $itemsHtml .= '<div class="fn-card__sub">' . esc($subtitle) . '</div>';
                }
                if ($href !== '') {
                    $itemsHtml .= '<div class="fn-card__mail">' . esc(preg_replace('/^mailto:/', '', $href) ?? $href) . '</div>';
                }
                $itemsHtml .= '</' . $tag . '>';
            }
        }

        $coreLabel = trim((string) ($b['core_label'] ?? ''));
        $coreSubtitle = trim((string) ($b['core_subtitle'] ?? ''));
        $coreHref = trim((string) ($b['core_href'] ?? ''));
        if ($itemsHtml === '' && $coreLabel === '') {
            return '';
        }

        $html = '<section class="section section--structure"><div class="section__inner">';
        $html .= '<div class="hub"><div class="hub__core">';
        if ($coreLabel !== '') {
            $html .= '<div class="hub__label">' . esc($coreLabel) . '</div>';
        }
        if ($coreSubtitle !== '') {
            $html .= '<div class="hub__sub">' . esc($coreSubtitle) . '</div>';
        }
        if ($coreHref !== '') {
            $html .= '<a href="' . esc($coreHref, 'attr') . '" class="hub__mail">' . esc(preg_replace('/^mailto:/', '', $coreHref) ?? $coreHref) . '</a>';
        }
        $html .= '</div><div class="hub__grid">' . $itemsHtml . '</div></div></div></section>';

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function contactGrid(array $b): string
    {
        $itemsHtml = '';
        $items = $b['items'] ?? [];
        if (is_array($items)) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $label = trim((string) ($item['label'] ?? ''));
                $title = trim((string) ($item['title'] ?? ''));
                $subtitle = trim((string) ($item['subtitle'] ?? ''));
                $href = trim((string) ($item['href'] ?? ''));
                if ($label === '' && $title === '' && $subtitle === '' && $href === '') {
                    continue;
                }
                $tag = $href !== '' ? 'a' : 'div';
                $hrefAttr = $href !== '' ? ' href="' . esc($href, 'attr') . '"' : '';
                $itemsHtml .= '<' . $tag . $hrefAttr . ' class="contact-block">';
                if ($label !== '') {
                    $itemsHtml .= '<div class="contact-block__label">' . esc($label) . '</div>';
                }
                if ($title !== '') {
                    $itemsHtml .= '<div class="contact-block__mail">' . esc($title) . '</div>';
                }
                if ($subtitle !== '') {
                    $itemsHtml .= '<div class="contact-block__sub">' . esc($subtitle) . '</div>';
                }
                $itemsHtml .= '</' . $tag . '>';
            }
        }
        if ($itemsHtml === '') {
            return '';
        }

        return '<section class="section section--contact"><div class="section__inner">'
            . '<div class="contact-card"><div class="contact-card__inner"><div class="contact-grid">' . $itemsHtml . '</div></div></div>'
            . '</div></section>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function ctaPanel(array $b): string
    {
        $text = trim((string) ($b['text'] ?? ''));
        $actionsHtml = '';
        $actions = $b['actions'] ?? [];
        if (is_array($actions)) {
            foreach ($actions as $action) {
                if (! is_array($action)) {
                    continue;
                }
                $label = trim((string) ($action['label'] ?? ''));
                $href = trim((string) ($action['href'] ?? ''));
                if ($label === '' || $href === '') {
                    continue;
                }
                $variant = strtolower(trim((string) ($action['variant'] ?? 'primary')));
                $class = $variant === 'secondary' ? 'btn btn--secondary' : 'btn btn--primary';
                $actionsHtml .= '<a href="' . esc($href, 'attr') . '" class="' . esc($class, 'attr') . '">' . esc($label) . '</a>';
            }
        }
        if ($text === '' && $actionsHtml === '') {
            return '';
        }

        return '<section class="section section--cms-cta"><div class="section__inner"><div class="section__cta">'
            . ($text !== '' ? '<p class="section__cta-lead">' . esc($text) . '</p>' : '')
            . ($actionsHtml !== '' ? '<div class="section__btn-row">' . $actionsHtml . '</div>' : '')
            . '</div></div></section>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function legalProse(array $b): string
    {
        $sectionsHtml = '';
        $sections = $b['sections'] ?? [];
        if (is_array($sections)) {
            foreach ($sections as $section) {
                if (! is_array($section)) {
                    continue;
                }
                $heading = trim((string) ($section['heading'] ?? ''));
                $body = trim((string) ($section['body'] ?? ''));
                $bullets = $section['bullets'] ?? [];
                if ($heading !== '') {
                    $sectionsHtml .= '<h2>' . esc($heading) . '</h2>';
                }
                foreach (self::textLines($body) as $paragraph) {
                    $sectionsHtml .= '<p>' . esc($paragraph) . '</p>';
                }
                $lis = '';
                if (is_array($bullets)) {
                    foreach ($bullets as $bullet) {
                        $bullet = trim((string) $bullet);
                        if ($bullet !== '') {
                            $lis .= '<li>' . esc($bullet) . '</li>';
                        }
                    }
                }
                if ($lis !== '') {
                    $sectionsHtml .= '<ul>' . $lis . '</ul>';
                }
            }
        }
        if ($sectionsHtml === '') {
            return '';
        }

        return '<section class="section section--legal"><div class="section__inner"><div class="ggz-legal-prose">' . $sectionsHtml . '</div></div></section>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function sources(array $b): string
    {
        $linesHtml = '';
        $lines = $b['lines'] ?? [];
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line !== '') {
                    $linesHtml .= '<li>' . esc($line) . '</li>';
                }
            }
        }
        if ($linesHtml === '') {
            return '';
        }

        return '<section class="section section--cms-sources"><div class="section__inner"><div class="section__source"><ul>' . $linesHtml . '</ul></div></div></section>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function sectorsGrid(array $b): string
    {
        $grid = function_exists('cms_sectors_render_tile_grid_html') ? cms_sectors_render_tile_grid_html() : '';
        if ($grid === '') {
            return '';
        }

        return '<section class="section section--secteurs"><div class="section__inner">'
            . $grid
            . '</div></section>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function footerColumns(array $b): string
    {
        $html = '';
        $columns = $b['columns'] ?? [];
        if (! is_array($columns)) {
            return '';
        }
        foreach ($columns as $column) {
            if (! is_array($column)) {
                continue;
            }
            $title = trim((string) ($column['title'] ?? ''));
            $linksHtml = '';
            $links = $column['links'] ?? [];
            if (is_array($links)) {
                foreach ($links as $link) {
                    if (! is_array($link)) {
                        continue;
                    }
                    $label = trim((string) ($link['label'] ?? ''));
                    if ($label === '') {
                        continue;
                    }
                    $soon = (int) ($link['soon'] ?? 0) === 1;
                    $href = trim((string) ($link['href'] ?? ''));
                    if ($soon) {
                        $linksHtml .= '<li><span class="footer__soon">' . esc($label) . '</span></li>';
                    } elseif ($href !== '') {
                        $linksHtml .= '<li><a href="' . esc($href, 'attr') . '">' . esc($label) . '</a></li>';
                    } else {
                        $linksHtml .= '<li>' . esc($label) . '</li>';
                    }
                }
            }
            if ($title === '' && $linksHtml === '') {
                continue;
            }
            $html .= '<div class="footer__col">';
            if ($title !== '') {
                $html .= '<h4>' . esc($title) . '</h4>';
            }
            if ($linksHtml !== '') {
                $html .= '<ul>' . $linksHtml . '</ul>';
            }
            $html .= '</div>';
        }

        return $html;
    }
}
