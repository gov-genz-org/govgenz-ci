<?php

declare(strict_types=1);

use App\Libraries\SiteContext;
use App\Models\DeclarationItemModel;

if (! function_exists('declaration_list_url')) {
    function declaration_list_url(): string
    {
        helper('locale');

        return SiteContext::declarationPathPrefixEnabled()
            ? localized_site_url('declaration')
            : localized_site_url('');
    }
}

if (! function_exists('declaration_public_url')) {
    function declaration_public_url(string $slug): string
    {
        helper('locale');
        $slug = strtolower(trim($slug, '/'));
        if ($slug === '') {
            return declaration_list_url();
        }
        if (SiteContext::declarationPathPrefixEnabled()) {
            return localized_site_url('declaration/' . $slug);
        }

        return localized_site_url($slug);
    }
}

if (! function_exists('declaration_kind_band_class')) {
    function declaration_kind_band_class(string $kind): string
    {
        return match ($kind) {
            DeclarationItemModel::KIND_PLEDGE      => 'pledge',
            DeclarationItemModel::KIND_ALERT      => 'alert',
            DeclarationItemModel::KIND_PARTNERSHIP => 'partnership',
            default                                => 'official',
        };
    }
}

if (! function_exists('declaration_default_band_label')) {
    function declaration_default_band_label(string $kind, string $locale = 'fr'): string
    {
        if ($locale === 'en') {
            return match ($kind) {
                DeclarationItemModel::KIND_PLEDGE      => 'Advocacy',
                DeclarationItemModel::KIND_ALERT      => 'Public alert',
                DeclarationItemModel::KIND_PARTNERSHIP => 'Partnership',
                default                                => 'Official declaration',
            };
        }

        return match ($kind) {
            DeclarationItemModel::KIND_PLEDGE      => 'Plaidoyer',
            DeclarationItemModel::KIND_ALERT      => 'Alerte publique',
            DeclarationItemModel::KIND_PARTNERSHIP => 'Partenariat',
            default                                => 'Déclaration officielle',
        };
    }
}

if (! function_exists('declaration_format_published_meta')) {
    function declaration_format_published_meta(?string $raw, string $metaLine, string $locale): string
    {
        $metaLine = trim($metaLine);
        if ($metaLine !== '') {
            return $metaLine;
        }

        $raw = trim((string) ($raw ?? ''));
        if ($raw === '') {
            return '';
        }

        try {
            $dt = new DateTimeImmutable($raw);
            if ($locale === 'en') {
                return $dt->format('F Y');
            }

            $months = [
                1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
                5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
                9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
            ];
            $m = (int) $dt->format('n');

            return ucfirst($months[$m] ?? $dt->format('m')) . ' ' . $dt->format('Y');
        } catch (Exception $e) {
            return '';
        }
    }
}

if (! function_exists('declaration_public_absolute_url')) {
    function declaration_public_absolute_url(string $slug): string
    {
        helper('url');
        $url = declaration_public_url($slug);
        if (str_starts_with($url, '/')) {
            return rtrim((string) base_url(), '/') . $url;
        }

        return $url;
    }
}

if (! function_exists('declaration_body_html')) {
    function declaration_body_html(array $item, string $locale = 'fr'): string
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

        $html = \App\Libraries\ProjectBodyBlocksRenderer::render($blocks, $locale);

        return str_replace('class="project-main"', 'class="declaration-main"', $html);
    }
}

if (! function_exists('declaration_share_qr_image_path')) {
    function declaration_share_qr_image_path(string $slug): string
    {
        $slug = strtolower(trim($slug, '/'));
        if (SiteContext::declarationPathPrefixEnabled()) {
            return 'declaration/' . $slug . '/share-qr.png';
        }

        return $slug . '/share-qr.png';
    }
}

if (! function_exists('declaration_share_qr_page_path')) {
    function declaration_share_qr_page_path(string $slug): string
    {
        $slug = strtolower(trim($slug, '/'));
        if (SiteContext::declarationPathPrefixEnabled()) {
            return 'declaration/' . $slug . '/share';
        }

        return $slug . '/share';
    }
}

if (! function_exists('declaration_share_qr_absolute_url')) {
    function declaration_share_qr_absolute_url(string $relativePath): string
    {
        helper('locale');
        $url = localized_site_url($relativePath);
        if (str_starts_with($url, '/')) {
            $url = rtrim((string) base_url(), '/') . $url;
        }

        return $url;
    }
}

if (! function_exists('declaration_share_qr_image_url')) {
    function declaration_share_qr_image_url(string $slug): string
    {
        $url = declaration_share_qr_absolute_url(declaration_share_qr_image_path($slug));
        helper('asset');
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . rawurlencode(front_asset_version());
    }
}

if (! function_exists('declaration_share_qr_page_url')) {
    function declaration_share_qr_page_url(string $slug): string
    {
        return declaration_share_qr_absolute_url(declaration_share_qr_page_path($slug));
    }
}

if (! function_exists('declaration_show_action_ctas')) {
    /**
     * @return list<array{label: string, href: string, variant: string}>
     */
    function declaration_show_action_ctas(array $item, string $title): array
    {
        $ctas      = [];
        $ctaLabel  = trim((string) ($item['cta_label'] ?? ''));
        $ctaHref   = trim((string) ($item['cta_href'] ?? ''));
        $contact   = 'contact@govgenz.org';

        if ($ctaHref !== '' && $ctaLabel !== '') {
            $variant = str_starts_with($ctaHref, 'mailto:') ? 'teal' : 'red';
            $ctas[]  = [
                'label'   => $ctaLabel,
                'href'    => $ctaHref,
                'variant' => $variant,
            ];
        }

        $ctas[] = [
            'label'   => lang('Declaration.cta_contact'),
            'href'    => 'mailto:' . $contact . '?subject=' . rawurlencode($title),
            'variant' => 'ghost',
        ];

        return $ctas;
    }
}

if (! function_exists('declaration_partner_slug_for_locale_switch')) {
    /**
     * Slug publié de la déclaration dans l’autre langue (même translation_group).
     */
    function declaration_partner_slug_for_locale_switch(string $slug, string $currentLocale): ?string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || preg_match('/^[a-z0-9\-]+$/', $slug) !== 1) {
            return null;
        }

        $loc   = $currentLocale === 'en' ? 'en' : 'fr';
        $other = $loc === 'en' ? 'fr' : 'en';

        $model = model(DeclarationItemModel::class);
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
            ->where('publication_state', DeclarationItemModel::PUBLICATION_PUBLISHED)
            ->first();

        if ($partner === null || ! is_array($partner)) {
            return null;
        }

        $out = strtolower(trim((string) ($partner['slug'] ?? '')));

        return $out !== '' && preg_match('/^[a-z0-9\-]+$/', $out) === 1 ? $out : null;
    }
}

if (! function_exists('declaration_split_items_by_section')) {
    /**
     * @param list<array<string, mixed>> $items
     *
     * @return array{declarations: list<array<string, mixed>>, partnerships: list<array<string, mixed>>}
     */
    function declaration_split_items_by_section(array $items): array
    {
        $declarations = [];
        $partnerships = [];

        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (($row['list_section'] ?? '') === DeclarationItemModel::SECTION_PARTNERSHIPS) {
                $partnerships[] = $row;
            } else {
                $declarations[] = $row;
            }
        }

        return ['declarations' => $declarations, 'partnerships' => $partnerships];
    }
}
