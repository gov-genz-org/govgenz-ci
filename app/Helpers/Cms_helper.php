<?php

declare(strict_types=1);

/**
 * CMS public — charger avec helper('cms') depuis les contrôleurs Front ou les vues admin.
 */
if (! function_exists('cms_layout_main_class')) {
    /**
     * Classe CSS pour <main> selon layout_key éditorial (colonne étroite / pleine largeur).
     */
    function cms_layout_main_class(?string $layoutKey): string
    {
        $k = strtolower(trim((string) ($layoutKey ?? '')));

        return match ($k) {
            '', 'default', 'home' => '',
            'narrow', 'etroit', 'étroit' => 'ggz-layout-narrow',
            'full', 'fullwidth', 'pleine-largeur', 'wide' => 'ggz-layout-full',
            default => '',
        };
    }
}

if (! function_exists('cms_layout_normalized')) {
    /**
     * Valeur à stocker en base : null (défaut), narrow, full, ou chaîne personnalisée (≤ 64).
     */
    function cms_layout_normalized(?string $raw): ?string
    {
        $raw = trim((string) ($raw ?? ''));
        if ($raw === '') {
            return null;
        }

        $l = strtolower($raw);

        return match ($l) {
            'default', 'home' => null,
            'narrow', 'etroit', 'étroit' => 'narrow',
            'full', 'fullwidth', 'pleine-largeur', 'wide' => 'full',
            default => mb_strlen($raw) <= 64 ? $raw : mb_substr($raw, 0, 64),
        };
    }
}

if (! function_exists('cms_page_suppress_outer_hero')) {
    /**
     * Pages dont le corps HTML reprend déjà l’en-tête « template » (site_govgenz),
     * sans bandeau compact titre+h1 du gabarit CMS (slugs FR et équivalents EN).
     */
    function cms_page_suppress_outer_hero(?string $slug): bool
    {
        $slug = strtolower(trim((string) ($slug ?? '')));

        return in_array($slug, [
            'qui-sommes-nous',
            'who-we-are',
            'notre-adn',
            'our-dna',
            'structure',
            'secteurs',
            'sectors',
            'etude',
            'study',
            'contact',
        ], true);
    }
}

if (! function_exists('cms_media_public_url')) {
    /**
     * URL publique d’un fichier en médiathèque (uploads/cms/…).
     */
    function cms_media_public_url(?int $id): ?string
    {
        if ($id === null || $id <= 0) {
            return null;
        }

        $row = model(\App\Models\CmsMediaModel::class)->find($id);
        if ($row === null) {
            return null;
        }

        $fn = trim((string) ($row['stored_filename'] ?? ''));
        if ($fn === '') {
            return null;
        }

        return base_url('uploads/cms/' . $fn);
    }
}

if (! function_exists('cms_page_structured_hero_active')) {
    /**
     * Au moins un champ hero structuré est renseigné (bandeau éditorial hors corps HTML).
     */
    function cms_page_structured_hero_active(array $page): bool
    {
        if (trim((string) ($page['hero_overline'] ?? '')) !== '') {
            return true;
        }
        if (trim((string) ($page['hero_title'] ?? '')) !== '') {
            return true;
        }
        if (trim((string) ($page['hero_lead'] ?? '')) !== '') {
            return true;
        }

        $raw = $page['hero_image_id'] ?? null;
        $id  = ($raw !== null && $raw !== '') ? (int) $raw : 0;

        return $id > 0;
    }
}

if (! function_exists('cms_render_structured_page_hero')) {
    /**
     * Bandeau hero depuis colonnes cms_pages (vide si aucun champ utile).
     */
    function cms_render_structured_page_hero(array $page): string
    {
        if (! cms_page_structured_hero_active($page)) {
            return '';
        }

        return view('front/partials/page_structured_hero', ['page' => $page]);
    }
}

if (! function_exists('cms_layout_select_state')) {
    /**
     * @return array{value: string, legacy: bool}
     */
    function cms_layout_select_state(?string $dbLayout): array
    {
        $raw = trim((string) ($dbLayout ?? ''));
        if ($raw === '') {
            return ['value' => '', 'legacy' => false];
        }

        $l = strtolower($raw);
        if (in_array($l, ['default', 'home'], true)) {
            return ['value' => '', 'legacy' => false];
        }
        if (in_array($l, ['narrow', 'etroit', 'étroit'], true)) {
            return ['value' => 'narrow', 'legacy' => false];
        }
        if (in_array($l, ['full', 'fullwidth', 'pleine-largeur', 'wide'], true)) {
            return ['value' => 'full', 'legacy' => false];
        }

        return ['value' => $raw, 'legacy' => true];
    }
}

if (! function_exists('cms_page_content_mode')) {
    /**
     * Mode de contenu principal pour une ligne cms_pages.
     */
    function cms_page_content_mode(array $page): string
    {
        $m = strtolower(trim((string) ($page['content_mode'] ?? '')));

        return ($m === 'blocks') ? 'blocks' : 'html';
    }
}

if (! function_exists('cms_footer_embed_slug')) {
    /**
     * Slug réservé : page CMS publiée dont le corps remplace les colonnes du pied de page (FR et EN).
     * L’URL /site-footer n’est pas exposée publiquement.
     */
    function cms_footer_embed_slug(): string
    {
        return 'site-footer';
    }
}

if (! function_exists('cms_render_page_body')) {
    /**
     * Corps principal affiché sur le site (HTML depuis éditeur ou blocs structurés).
     */
    function cms_render_page_body(array $page): string
    {
        if (cms_page_content_mode($page) === 'blocks') {
            $raw = $page['body_blocks'] ?? null;
            if ($raw === null || $raw === '') {
                return '';
            }

            $decoded = json_decode((string) $raw, true);
            if (! is_array($decoded) || $decoded === []) {
                return '';
            }

            return \App\Libraries\CmsBodyBlocksRenderer::render($decoded);
        }

        return (string) ($page['body_html'] ?? '');
    }
}

if (! function_exists('cms_format_publish_date')) {
    /**
     * Affiche une date de publication lisible (site public).
     */
    function cms_format_publish_date(?string $raw): string
    {
        $raw = trim((string) ($raw ?? ''));
        if ($raw === '') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($raw))->format('d/m/Y');
        } catch (\Throwable) {
            return $raw;
        }
    }
}
