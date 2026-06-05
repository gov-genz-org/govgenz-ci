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

if (! function_exists('declaration_mailto_href')) {
    function declaration_mailto_href(string $email, string $subject, ?string $body = null): string
    {
        $email = trim($email);
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '';
        }

        $query = 'subject=' . rawurlencode($subject);
        if ($body !== null && trim($body) !== '') {
            $query .= '&body=' . rawurlencode(trim($body));
        }

        return 'mailto:' . $email . '?' . $query;
    }
}

if (! function_exists('declaration_default_cta_for_item')) {
    /**
     * Libellé + lien action principal pour une fiche (évite « Nous soutenir » + mailto générique).
     *
     * @return array{label: string, href: string}|null
     */
    function declaration_default_cta_for_item(array $item, string $title): ?array
    {
        $kind    = (string) ($item['kind'] ?? DeclarationItemModel::KIND_OFFICIAL);
        $section = (string) ($item['list_section'] ?? DeclarationItemModel::SECTION_DECLARATIONS);
        $ctaHref = trim((string) ($item['cta_href'] ?? ''));
        $ctaLabel = trim((string) ($item['cta_label'] ?? ''));

        if ($ctaHref === '') {
            return null;
        }

        $subject = lang('Declaration.cta_email_subject_prefix') . $title;

        if (str_starts_with($ctaHref, 'mailto:')) {
            $email = strtolower(trim((string) preg_replace('#^mailto:#i', '', explode('?', $ctaHref, 2)[0])));
            if ($email === '') {
                return null;
            }
            $href = declaration_mailto_href($email, $subject);
            if ($href === '') {
                return null;
            }

            $vague = [
                'nous soutenir', 'nous contacter', 'support us', 'contact us',
                'en savoir plus', 'learn more', 'lire la position complète', 'read full position',
            ];
            $labelNorm = function_exists('mb_strtolower') ? mb_strtolower($ctaLabel) : strtolower($ctaLabel);
            if ($ctaLabel !== '' && ! in_array($labelNorm, $vague, true)) {
                return ['label' => $ctaLabel, 'href' => $href];
            }

            if ($section === DeclarationItemModel::SECTION_PARTNERSHIPS
                || str_contains($email, 'partnerships@')) {
                return ['label' => lang('Declaration.cta_partnership'), 'href' => $href];
            }

            return match ($kind) {
                DeclarationItemModel::KIND_PLEDGE => [
                    'label' => lang('Declaration.cta_support_pledge'),
                    'href'  => $href,
                ],
                DeclarationItemModel::KIND_ALERT => [
                    'label' => lang('Declaration.cta_alert'),
                    'href'  => $href,
                ],
                default => [
                    'label' => lang('Declaration.cta_support_generic'),
                    'href'  => $href,
                ],
            };
        }

        if ($ctaLabel === '') {
            return null;
        }

        return ['label' => $ctaLabel, 'href' => $ctaHref];
    }
}

if (! function_exists('declaration_cta_panel_action_label')) {
    /**
     * Libellés lisibles pour le bloc CMS cta_panel (évite boutons = adresses e-mail brutes).
     */
    function declaration_cta_panel_action_label(string $label, string $href): string
    {
        $label = trim($label);
        if ($label !== '' && ! str_contains($label, '@')) {
            return $label;
        }

        $hrefLower = strtolower($href);
        if (str_contains($hrefLower, 'partnerships@')) {
            return lang('Declaration.cta_panel_partnerships');
        }

        return lang('Declaration.cta_panel_contact');
    }
}

if (! function_exists('declaration_show_action_ctas')) {
    /**
     * @return list<array{label: string, href: string, variant: string}>
     */
    function declaration_show_action_ctas(array $item, string $title): array
    {
        helper('locale');
        $ctas   = [];
        $primary = declaration_default_cta_for_item($item, $title);

        if ($primary !== null) {
            $href = $primary['href'];
            $variant = str_starts_with($href, 'mailto:') ? 'teal' : 'red';
            $ctas[] = [
                'label'   => $primary['label'],
                'href'    => $href,
                'variant' => $variant,
            ];
        }

        $ctas[] = [
            'label'   => lang('Declaration.cta_contact_form'),
            'href'    => localized_site_url('contact'),
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
