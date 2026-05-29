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

if (! function_exists('cms_list_hero_page_slugs')) {
    /**
     * Slugs CMS des bandeaux de listes publiques (identiques FR/EN, même URL que la liste).
     *
     * @return list<string>
     */
    function cms_list_hero_page_slugs(): array
    {
        return ['press', 'projects', 'positions'];
    }
}

if (! function_exists('cms_list_hero_page_kind')) {
    /**
     * Type de liste si le slug est un bandeau programme (y compris anciens slugs avant migration).
     */
    function cms_list_hero_page_kind(?string $slug): ?string
    {
        $slug = strtolower(trim((string) ($slug ?? '')));

        return match (true) {
            in_array($slug, ['press', 'presse-programme', 'press-program'], true) => 'press',
            in_array($slug, ['projects', 'projets-programme', 'projects-program'], true) => 'projects',
            in_array($slug, ['positions', 'positions-programme', 'positions-program'], true) => 'positions',
            default => null,
        };
    }
}

if (! function_exists('cms_is_list_hero_page_slug')) {
    function cms_is_list_hero_page_slug(?string $slug): bool
    {
        return cms_list_hero_page_kind($slug) !== null;
    }
}

if (! function_exists('cms_list_hero_canonical_slug')) {
    function cms_list_hero_canonical_slug(string $kind): string
    {
        return \App\Libraries\CmsListHeroPageAdmin::canonicalSlug($kind);
    }
}

if (! function_exists('cms_positions_list_page_slug')) {
    function cms_positions_list_page_slug(): string
    {
        return 'positions';
    }
}

if (! function_exists('cms_projects_list_page_slug')) {
    /**
     * Slug CMS du bandeau de la liste publique /projects (champs hero uniquement).
     */
    function cms_projects_list_page_slug(): string
    {
        return 'projects';
    }
}

if (! function_exists('cms_press_list_page_slug')) {
    /**
     * Slug CMS du bandeau de la liste publique /press (champs hero uniquement).
     */
    function cms_press_list_page_slug(): string
    {
        return 'press';
    }
}

if (! function_exists('cms_list_hero_page_row')) {
    /**
     * @return array<string, mixed>|null
     */
    function cms_list_hero_page_row(string $kind, string $locale): ?array
    {
        $canonical = cms_list_hero_canonical_slug($kind);
        if ($canonical === '') {
            return null;
        }

        $locale = $locale === 'en' ? 'en' : 'fr';

        return model(\App\Models\CmsPageModel::class)
            ->where('slug', $canonical)
            ->where('locale', $locale)
            ->first();
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

        return \App\Libraries\CmsMediaStorage::publicUrl($fn);
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

if (! function_exists('cms_page_partner_slug_for_locale_switch')) {
    /**
     * Slug publié de la page CMS dans l’autre langue, si les deux lignes partagent le même
     * `translation_group` (sinon null — le switch retombe sur le mappage fixe de segments).
     */
    function cms_page_partner_slug_for_locale_switch(string $slug, string $currentLocale): ?string
    {
        $slug = trim($slug);
        if ($slug === '' || strcasecmp($slug, cms_footer_embed_slug()) === 0) {
            return null;
        }

        $loc = $currentLocale === 'en' ? 'en' : 'fr';
        $other = $loc === 'en' ? 'fr' : 'en';

        $model = model(\App\Models\CmsPageModel::class);
        $row   = $model->where('slug', $slug)
            ->where('locale', $loc)
            ->where('status', 'published')
            ->first();
        if ($row === null || ! is_array($row)) {
            return null;
        }

        $tg = trim((string) ($row['translation_group'] ?? ''));
        if ($tg === '') {
            return null;
        }

        $partner = $model->where('translation_group', $tg)
            ->where('locale', $other)
            ->where('status', 'published')
            ->first();
        if ($partner === null || ! is_array($partner)) {
            return null;
        }

        $out = trim((string) ($partner['slug'] ?? ''));

        return $out !== '' ? $out : null;
    }
}

if (! function_exists('cms_sectors_static_sample_tile_grid_html')) {
    /**
     * Exemple statique pour l’aide admin quand la table sectors est absente ou vide.
     */
    function cms_sectors_static_sample_tile_grid_html(): string
    {
        return <<<'HTML'
<div class="tile-grid">
    <a href="mailto:education@govgenz.org" class="tile reveal" data-delay="0">
        <div class="tile__name">EDUCATION</div>
        <div class="tile__sub">Formation · Recherche</div>
        <div class="tile__mail">education@govgenz.org</div>
    </a>
    <a href="mailto:legal@govgenz.org" class="tile reveal" data-delay="40">
        <div class="tile__name">LEGAL</div>
        <div class="tile__sub">Droit · Institutions</div>
        <div class="tile__mail">legal@govgenz.org</div>
    </a>
</div>
HTML;
    }
}

if (! function_exists('cms_sectors_render_tile_grid_html')) {
    /**
     * Grille des secteurs depuis la table `sectors` (même source que Join et les projets).
     */
    function cms_sectors_render_tile_grid_html(): string
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('sectors')) {
            return '';
        }

        $sectors = model(\App\Models\SectorModel::class)->listOrdered();

        return view('front/sectors/tile_grid', ['sectors' => $sectors]);
    }
}

if (! function_exists('cms_sectors_guide_preview_html')) {
    /**
     * Aperçu admin (aide HTML / blocs) : grille BDD ou exemple statique si vide.
     */
    function cms_sectors_guide_preview_html(): string
    {
        $grid = cms_sectors_render_tile_grid_html();
        if ($grid !== '' && str_contains($grid, 'tile-grid')) {
            return $grid;
        }

        return cms_sectors_static_sample_tile_grid_html();
    }
}

if (! function_exists('cms_sectors_guide_preview_body')) {
    /**
     * Corps d’aperçu admin : remplace les marqueurs sans requête BDD si la table sectors manque.
     */
    function cms_sectors_guide_preview_body(string $html): string
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('sectors')) {
            $body = cms_apply_html_embeds($html);
            if (str_contains($body, 'tile-grid')) {
                return $body;
            }
        }

        if (str_contains($html, 'tile-grid')) {
            return $html;
        }

        return cms_sectors_guide_preview_html();
    }
}

if (! function_exists('cms_sector_tile_placeholder_div_patterns')) {
    /**
     * Motifs pour un marqueur div (TinyMCE peut ajouter &nbsp;, &quot;, &lt;br&gt;, etc.).
     *
     * @return list<string>
     */
    function cms_sector_tile_placeholder_div_patterns(string $key): array
    {
        $q     = preg_quote($key, '~');
        $quote = '(?:"|\'|&quot;|&#34;|&apos;|&#39;)';
        $val   = $quote . $q . $quote;
        $in    = '(?=[^>]*\bdata-gg-cms\s*=\s*' . $val . ')';
        $inner = '(?:\s|&nbsp;|&#160;|&#x0*A0;|<br\s*/?>)*';

        return [
            '~<div\b' . $in . '[^>]*>' . $inner . '</div>~iu',
            '~<div\b' . $in . '[^>]*/>~iu',
        ];
    }
}

if (! function_exists('cms_apply_html_embeds')) {
    /**
     * Remplace les marqueurs d’embed dans le HTML éditeur (mode source recommandé).
     *
     * Grille secteurs (table `sectors`) — valeur équivalente EN / FR :
     * - data-gg-cms="sectors-tile-grid"
     * - data-gg-cms="secteurs-tile-grid"
     * - <!-- GG_CMS_SECTORS_TILE_GRID -->
     * - <!-- GG_CMS_SECTEURS_TILE_GRID -->
     *
     * Note : côté TinyMCE, `extended_valid_elements` pour `div` doit inclure `class` (et
     * `data-gg-cms`) : une règle du type `div[data-gg-cms]` seule remplace la règle `div` et
     * supprime toutes les classes au save.
     */
    function cms_apply_html_embeds(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $gridHtml = cms_sectors_render_tile_grid_html();
        if ($gridHtml === '') {
            return $html;
        }

        $patterns = [];

        foreach (['sectors-tile-grid', 'secteurs-tile-grid'] as $key) {
            $patterns = array_merge($patterns, cms_sector_tile_placeholder_div_patterns($key));
        }

        $patterns[] = '#<!--\s*GG_CMS_SECTORS_TILE_GRID\s*-->#i';
        $patterns[] = '#<!--\s*GG_CMS_SECTEURS_TILE_GRID\s*-->#i';

        foreach ($patterns as $pattern) {
            $html = preg_replace_callback(
                $pattern,
                static function () use ($gridHtml): string {
                    return $gridHtml;
                },
                $html,
            ) ?? $html;
        }

        return $html;
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

            $out = \App\Libraries\CmsBodyBlocksRenderer::render($decoded);

            return cms_apply_html_embeds($out);
        }

        return cms_apply_html_embeds((string) ($page['body_html'] ?? ''));
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
