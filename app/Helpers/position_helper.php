<?php

declare(strict_types=1);

use App\Libraries\ProjectBodyBlocksRenderer;
use App\Libraries\SiteContext;
use App\Models\PositionItemModel;

if (! function_exists('position_public_url')) {
    function position_public_url(string $slug): string
    {
        helper('locale');
        $slug = strtolower(trim($slug, '/'));
        if ($slug === '') {
            return SiteContext::positionsPathPrefixEnabled()
                ? localized_site_url('positions')
                : localized_site_url('');
        }
        if (SiteContext::positionsPathPrefixEnabled()) {
            return localized_site_url('positions/' . $slug);
        }

        return localized_site_url($slug);
    }
}

if (! function_exists('position_type_icon')) {
    function position_type_icon(string $code): string
    {
        return match ($code) {
            PositionItemModel::TYPE_DENIAL   => '🚫',
            PositionItemModel::TYPE_PRAISE   => '✅',
            PositionItemModel::TYPE_ANALYSIS => '🔍',
            PositionItemModel::TYPE_SOLUTION => '💡',
            default                          => '',
        };
    }
}

if (! function_exists('position_type_labels')) {
    /**
     * @return array<string, string>
     */
    function position_type_labels(?string $locale = null): array
    {
        $locale = $locale === 'en' ? 'en' : 'fr';

        return [
            PositionItemModel::TYPE_DENIAL   => lang('Positions.type_denial', [], $locale),
            PositionItemModel::TYPE_PRAISE   => lang('Positions.type_praise', [], $locale),
            PositionItemModel::TYPE_ANALYSIS => lang('Positions.type_analysis', [], $locale),
            PositionItemModel::TYPE_SOLUTION => lang('Positions.type_solution', [], $locale),
        ];
    }
}

if (! function_exists('position_type_filter_labels')) {
    /**
     * Libellés des pastilles filtre (icône + nom), alignés sur la maquette statique.
     *
     * @return array<string, string>
     */
    function position_type_filter_labels(?string $locale = null): array
    {
        $out = [];
        foreach (position_type_labels($locale) as $code => $label) {
            $icon = position_type_icon($code);
            $out[$code] = $icon !== '' ? trim($icon . ' ' . $label) : $label;
        }

        return $out;
    }
}

if (! function_exists('position_type_tip')) {
    function position_type_tip(string $code, ?string $locale = null): string
    {
        $locale = $locale === 'en' ? 'en' : 'fr';

        return match ($code) {
            PositionItemModel::TYPE_DENIAL   => lang('Positions.type_tip_denial', [], $locale),
            PositionItemModel::TYPE_PRAISE   => lang('Positions.type_tip_praise', [], $locale),
            PositionItemModel::TYPE_ANALYSIS => lang('Positions.type_tip_analysis', [], $locale),
            PositionItemModel::TYPE_SOLUTION => lang('Positions.type_tip_solution', [], $locale),
            default                          => '',
        };
    }
}

if (! function_exists('position_types_from_csv')) {
    /**
     * @return list<string>
     */
    function position_types_from_csv(string $csv): array
    {
        $allowed = array_fill_keys(PositionItemModel::typeCodes(), true);
        $out     = [];
        foreach (array_filter(array_map('trim', explode(',', strtolower($csv)))) as $code) {
            if (isset($allowed[$code])) {
                $out[$code] = true;
            }
        }

        return array_keys($out);
    }
}

if (! function_exists('position_type_band_label')) {
    function position_type_band_label(string $typesCsv): string
    {
        $labels = position_type_labels();
        $parts  = [];
        foreach (position_types_from_csv($typesCsv) as $code) {
            $icon = position_type_icon($code);
            $lab = $labels[$code] ?? $code;
            if ($lab !== '') {
                $parts[] = trim($icon . ' ' . $lab);
            }
        }

        return implode(' · ', $parts);
    }
}

if (! function_exists('position_type_band_class')) {
    function position_type_band_class(string $typesCsv): string
    {
        $types = position_types_from_csv($typesCsv);
        if ($types === []) {
            return 'analysis';
        }
        if (in_array(PositionItemModel::TYPE_DENIAL, $types, true)) {
            return 'denial';
        }
        if (in_array(PositionItemModel::TYPE_PRAISE, $types, true)) {
            return 'praise';
        }
        if (in_array(PositionItemModel::TYPE_SOLUTION, $types, true)) {
            return 'solution';
        }

        return 'analysis';
    }
}

if (! function_exists('position_body_html')) {
    function position_body_html(array $item, string $locale = 'fr'): string
    {
        if (! in_array($locale, ['fr', 'en'], true)) {
            $locale = 'fr';
        }

        $mode = strtolower(trim((string) ($item['body_content_mode'] ?? 'blocks')));
        if ($mode === 'html') {
            return (string) ($item['body'] ?? '');
        }

        $raw = trim((string) ($item['body_blocks'] ?? ''));
        if ($raw === '' || $raw === '[]') {
            return '';
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return '';
        }

        $blocks = [];
        foreach ($decoded as $b) {
            if (is_array($b)) {
                $blocks[] = $b;
            }
        }

        $html = ProjectBodyBlocksRenderer::render($blocks, $locale);

        return str_replace('class="project-main"', 'class="position-main"', $html);
    }
}

if (! function_exists('position_format_published_date')) {
    function position_format_published_date(?string $datetime, string $locale = 'fr'): string
    {
        if ($datetime === null || trim($datetime) === '') {
            return '';
        }
        $ts = strtotime($datetime);
        if ($ts === false) {
            return '';
        }

        if ($locale === 'en') {
            return date('M j, Y', $ts);
        }

        $months = [
            1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.', 5 => 'mai', 6 => 'juin',
            7 => 'juil.', 8 => 'août', 9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.',
        ];
        $d = (int) date('j', $ts);
        $m = (int) date('n', $ts);
        $y = date('Y', $ts);

        return $d . ' ' . ($months[$m] ?? '') . ' ' . $y;
    }
}

if (! function_exists('position_public_absolute_url')) {
    function position_public_absolute_url(string $slug): string
    {
        helper('url');
        $url = position_public_url($slug);
        if (str_starts_with($url, '/')) {
            return rtrim((string) base_url(), '/') . $url;
        }

        return $url;
    }
}

if (! function_exists('position_share_qr_image_path')) {
    function position_share_qr_image_path(string $slug): string
    {
        $slug = strtolower(trim($slug, '/'));
        if (SiteContext::positionsPathPrefixEnabled()) {
            return 'positions/' . $slug . '/share-qr.png';
        }

        return $slug . '/share-qr.png';
    }
}

if (! function_exists('position_share_qr_page_path')) {
    function position_share_qr_page_path(string $slug): string
    {
        $slug = strtolower(trim($slug, '/'));
        if (SiteContext::positionsPathPrefixEnabled()) {
            return 'positions/' . $slug . '/share';
        }

        return $slug . '/share';
    }
}

if (! function_exists('position_share_qr_absolute_url')) {
    function position_share_qr_absolute_url(string $relativePath): string
    {
        helper('locale');
        $url = localized_site_url($relativePath);
        if (str_starts_with($url, '/')) {
            $url = rtrim((string) base_url(), '/') . $url;
        }

        return $url;
    }
}

if (! function_exists('position_share_qr_image_url')) {
    function position_share_qr_image_url(string $slug): string
    {
        $url = position_share_qr_absolute_url(position_share_qr_image_path($slug));
        helper('asset');
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . rawurlencode(front_asset_version());
    }
}

if (! function_exists('position_share_qr_page_url')) {
    function position_share_qr_page_url(string $slug): string
    {
        return position_share_qr_absolute_url(position_share_qr_page_path($slug));
    }
}

if (! function_exists('position_show_action_ctas')) {
    /**
     * Boutons d’action fiche (alignés fiche projet : rouge, teal, ghost).
     * Signer si alerte ; Soutenir en 3ᵉ position, style « concept note » (ghost).
     * Le partage est dans le widget QR, pas ici.
     *
     * @return list<array{label: string, href: string, variant: string}>
     */
    function position_show_action_ctas(string $typesCsv, string $title): array
    {
        $types   = position_types_from_csv($typesCsv);
        $contact = 'contact@govgenz.org';
        $ctas    = [];

        if (in_array(PositionItemModel::TYPE_DENIAL, $types, true)) {
            $ctas[] = [
                'label'   => lang('Positions.show_cta_sign'),
                'href'    => 'mailto:' . $contact . '?subject=' . rawurlencode('Signer la demande — ' . $title),
                'variant' => 'red',
            ];
        }

        $ctas[] = [
            'label'   => lang('Positions.show_cta_expert'),
            'href'    => 'mailto:' . $contact . '?subject=' . rawurlencode('Contribuer comme expert — ' . $title),
            'variant' => 'teal',
        ];

        $ctas[] = [
            'label'   => lang('Positions.show_cta_support'),
            'href'    => 'mailto:' . $contact . '?subject=' . rawurlencode('Soutenir — ' . $title),
            'variant' => 'ghost',
        ];

        return $ctas;
    }
}

if (! function_exists('position_partner_slug_for_locale_switch')) {
    /**
     * Slug publié de la position dans l’autre langue (même translation_group).
     */
    function position_partner_slug_for_locale_switch(string $slug, string $currentLocale): ?string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || ! preg_match('/^[a-z0-9\-]+$/', $slug)) {
            return null;
        }

        $loc   = $currentLocale === 'en' ? 'en' : 'fr';
        $other = $loc === 'en' ? 'fr' : 'en';

        $model = model(PositionItemModel::class);
        $row   = $model->findPublishedBySlug($slug, $loc);
        if ($row === null) {
            return null;
        }

        $tg = trim((string) ($row['translation_group'] ?? ''));
        if ($tg === '') {
            return null;
        }

        $partner = $model->where('translation_group', $tg)
            ->where('locale', $other)
            ->where('publication_state', PositionItemModel::PUBLICATION_PUBLISHED)
            ->first();

        if ($partner === null || ! is_array($partner)) {
            return null;
        }

        $out = strtolower(trim((string) ($partner['slug'] ?? '')));

        return $out !== '' && preg_match('/^[a-z0-9\-]+$/', $out) === 1 ? $out : null;
    }
}

if (! function_exists('positions_program_filter_post_url')) {
    function positions_program_filter_post_url(): string
    {
        helper('locale');
        if (SiteContext::positionsPathPrefixEnabled()) {
            return localized_site_url('positions/filter');
        }

        return localized_site_url('filter');
    }
}

if (! function_exists('position_reading_label')) {
    function position_reading_label(array $item, string $locale = 'fr'): string
    {
        $min = (int) ($item['reading_minutes'] ?? 0);
        if ($min <= 0) {
            return '';
        }

        return $locale === 'en'
            ? $min . ' min read'
            : $min . ' min de lecture';
    }
}
