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

if (! function_exists('cms_list_page_layout_main_class')) {
    /**
     * Classe CSS pour <main> à partir d’une page CMS bandeau de liste (presse, projets…).
     *
     * @param array<string, mixed>|null $listPage
     */
    function cms_list_page_layout_main_class(?array $listPage): string
    {
        if ($listPage === null) {
            return 'ggz-layout-full';
        }

        return cms_layout_main_class(isset($listPage['layout_key']) ? (string) $listPage['layout_key'] : null);
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

if (! function_exists('cms_declaration_list_page_slug')) {
    function cms_declaration_list_page_slug(): string
    {
        return 'declaration';
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

if (! function_exists('cms_post_partner_slug_for_locale_switch')) {
    /**
     * Slug publié du communiqué presse dans l’autre langue (même translation_group).
     */
    function cms_post_partner_slug_for_locale_switch(string $slug, string $currentLocale): ?string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '' || ! preg_match('/^[a-z0-9\-]+$/', $slug)) {
            return null;
        }

        $loc   = $currentLocale === 'en' ? 'en' : 'fr';
        $other = $loc === 'en' ? 'fr' : 'en';

        $model = model(\App\Models\CmsPostModel::class);
        $row   = $model->getPublishedBySlug($slug, $loc);
        if ($row === null) {
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

        $out = strtolower(trim((string) ($partner['slug'] ?? '')));

        return $out !== '' && preg_match('/^[a-z0-9\-]+$/', $out) === 1 ? $out : null;
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

if (! function_exists('cms_sectors_public_slug')) {
    function cms_sectors_public_slug(): string
    {
        return \App\Libraries\SiteContext::locale() === 'en' ? 'sectors' : 'secteurs';
    }
}

if (! function_exists('cms_sectors_legacy_slugs')) {
    /**
     * @return list<string>
     */
    function cms_sectors_legacy_slugs(): array
    {
        return ['secteur', 'sector'];
    }
}

if (! function_exists('cms_sectors_get_published_page')) {
    /**
     * Page CMS publiée pour /secteurs (slug canonique + repli singulier).
     *
     * @return ?array<string, mixed>
     */
    function cms_sectors_get_published_page(): ?array
    {
        $pages = model(\App\Models\CmsPageModel::class);
        $page  = $pages->getPublishedBySlug(cms_sectors_public_slug());
        if ($page !== null) {
            return $page;
        }

        foreach (cms_sectors_legacy_slugs() as $legacy) {
            $page = $pages->getPublishedBySlug($legacy);
            if ($page !== null) {
                return $page;
            }
        }

        return null;
    }
}

if (! function_exists('cms_sectors_normalize_layout')) {
    function cms_sectors_normalize_layout(mixed $raw, string $default = 'compact'): string
    {
        $layout = strtolower(trim((string) $raw));
        if ($layout === 'tile' || $layout === 'card') {
            $layout = 'compact';
        }
        if (! in_array($layout, ['compact', 'wide'], true)) {
            return $default;
        }

        return $layout;
    }
}

if (! function_exists('cms_sectors_grid_layout_from_blocks')) {
    /**
     * @param list<array<string, mixed>> $blocks
     */
    function cms_sectors_grid_layout_from_blocks(array $blocks, string $default = 'wide'): string
    {
        foreach ($blocks as $blk) {
            if (! is_array($blk)) {
                continue;
            }
            if (strtolower(trim((string) ($blk['type'] ?? ''))) !== 'sectors_grid') {
                continue;
            }

            return cms_sectors_normalize_layout($blk['layout'] ?? null, $default);
        }

        return $default;
    }
}

if (! function_exists('cms_sectors_grid_layout_from_page')) {
    function cms_sectors_grid_layout_from_page(array $page, string $default = 'wide'): string
    {
        if (strtolower(trim((string) ($page['content_mode'] ?? ''))) !== 'blocks') {
            return $default;
        }

        $raw = trim((string) ($page['body_blocks'] ?? ''));
        if ($raw === '' || $raw === '[]') {
            return $default;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $default;
        }

        return cms_sectors_grid_layout_from_blocks($decoded, $default);
    }
}

if (! function_exists('cms_sectors_grid_block_from_blocks')) {
    /**
     * @param list<array<string, mixed>> $blocks
     * @return ?array<string, mixed>
     */
    function cms_sectors_grid_block_from_blocks(array $blocks): ?array
    {
        foreach ($blocks as $blk) {
            if (! is_array($blk)) {
                continue;
            }
            if (strtolower(trim((string) ($blk['type'] ?? ''))) !== 'sectors_grid') {
                continue;
            }

            return $blk;
        }

        return null;
    }
}

if (! function_exists('cms_sectors_grid_block_from_page')) {
    /**
     * @return ?array<string, mixed>
     */
    function cms_sectors_grid_block_from_page(array $page): ?array
    {
        if (strtolower(trim((string) ($page['content_mode'] ?? ''))) !== 'blocks') {
            return null;
        }

        $raw = trim((string) ($page['body_blocks'] ?? ''));
        if ($raw === '' || $raw === '[]') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return null;
        }

        return cms_sectors_grid_block_from_blocks($decoded);
    }
}

if (! function_exists('cms_block_render_section_intro_html')) {
    /**
     * @param array<string, mixed> $block
     */
    function cms_block_render_section_intro_html(array $block): string
    {
        $kicker = trim((string) ($block['kicker'] ?? ''));
        $title  = trim((string) ($block['title'] ?? ''));
        $lead   = trim((string) ($block['lead'] ?? ''));
        if ($kicker === '' && $title === '' && $lead === '') {
            return '';
        }

        $headingId = trim((string) ($block['heading_id'] ?? ''));
        $html      = '<header class="section__header">';
        if ($kicker !== '') {
            $html .= '<div class="section__overline">' . esc($kicker) . '</div>';
        }
        if ($title !== '') {
            $idAttr = $headingId !== '' ? ' id="' . esc($headingId, 'attr') . '"' : '';
            $html .= '<h2 class="section__title"' . $idAttr . '>' . esc($title) . '</h2>';
        }
        if ($lead !== '') {
            $html .= '<p class="section__lead">' . nl2br(esc($lead)) . '</p>';
        }
        $html .= '</header>';

        return $html;
    }
}

if (! function_exists('cms_block_render_teal_banner_html')) {
    /**
     * @param array<string, mixed> $block
     */
    function cms_block_render_teal_banner_html(array $block): string
    {
        $bannerTitle    = trim((string) ($block['banner_title'] ?? ''));
        $bannerSubtitle = trim((string) ($block['banner_subtitle'] ?? ''));
        if ($bannerTitle === '' && $bannerSubtitle === '') {
            return '';
        }

        $html = '<div class="decl-teams-header">';
        if ($bannerTitle !== '') {
            $html .= '<h3>' . esc($bannerTitle) . '</h3>';
        }
        if ($bannerSubtitle !== '') {
            $html .= '<span>' . esc($bannerSubtitle) . '</span>';
        }
        $html .= '</div>';

        return $html;
    }
}

if (! function_exists('cms_sectors_render_block_intro_html')) {
    /**
     * @param array<string, mixed> $block
     */
    function cms_sectors_render_block_intro_html(array $block): string
    {
        return cms_block_render_section_intro_html($block);
    }
}

if (! function_exists('cms_sectors_render_block_banner_html')) {
    /**
     * Bandeau teal (mode compact / Déclaration).
     *
     * @param array<string, mixed> $block
     */
    function cms_sectors_render_block_banner_html(array $block, string $layout = 'compact'): string
    {
        $layout = cms_sectors_normalize_layout($layout, 'compact');
        if ($layout !== 'compact') {
            return '';
        }

        return cms_block_render_teal_banner_html($block);
    }
}

if (! function_exists('cms_sectors_render_tile_grid_html')) {
    /**
     * Grille des secteurs depuis la table `sectors` (même source que Join et les projets).
     */
    function cms_sectors_render_tile_grid_html(string $layout = 'compact'): string
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('sectors')) {
            return '';
        }

        $layout = cms_sectors_normalize_layout($layout, 'compact');

        $sectors = model(\App\Models\SectorModel::class)->listOrdered();

        return view('front/sectors/tile_grid', [
            'sectors' => $sectors,
            'layout'  => $layout,
        ]);
    }
}

if (! function_exists('cms_structures_render_dept_grid_html')) {
    /**
     * Grille organigramme / départements depuis la table `structure_units`.
     */
    function cms_structures_render_dept_grid_html(): string
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('structure_units')) {
            return '';
        }

        $units = model(\App\Models\StructureUnitModel::class)->listFunctionsActive();
        if ($units === []) {
            return '';
        }

        return view('front/structures/dept_grid', ['units' => $units]);
    }
}

if (! function_exists('cms_structures_render_hub_html')) {
    /**
     * Hub noyau + fonctions depuis `structure_units`.
     */
    function cms_structures_render_hub_html(): string
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('structure_units')) {
            return '';
        }

        $model = model(\App\Models\StructureUnitModel::class);
        $functions = $model->listFunctionsActive();
        $core      = $model->findActiveCore();
        if ($core === null && $functions === []) {
            return '';
        }

        return view('front/structures/hub', [
            'core'      => $core,
            'functions' => $functions,
        ]);
    }
}

if (! function_exists('cms_structures_normalize_layout')) {
    function cms_structures_normalize_layout(mixed $raw, string $default = 'dept'): string
    {
        $layout = strtolower(trim((string) $raw));
        if (! in_array($layout, ['hub', 'dept'], true)) {
            return $default;
        }

        return $layout;
    }
}

if (! function_exists('cms_structures_render_block_intro_html')) {
    /**
     * @param array<string, mixed> $block
     */
    function cms_structures_render_block_intro_html(array $block): string
    {
        return cms_block_render_section_intro_html($block);
    }
}

if (! function_exists('cms_structures_render_block_banner_html')) {
    /**
     * Bandeau teal (mode cartes départements / Déclaration).
     *
     * @param array<string, mixed> $block
     */
    function cms_structures_render_block_banner_html(array $block, string $layout = 'dept'): string
    {
        $layout = cms_structures_normalize_layout($layout, 'dept');
        if ($layout !== 'dept') {
            return '';
        }

        return cms_block_render_teal_banner_html($block);
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
            $html = cms_apply_structures_html_embeds($html);

            return $html;
        }

        $wideGridHtml = cms_sectors_render_tile_grid_html('wide');

        $patterns = [];

        foreach (['sectors-tile-grid', 'secteurs-tile-grid'] as $key) {
            $patterns = array_merge($patterns, cms_sector_tile_placeholder_div_patterns($key));
        }

        $patterns[] = '#<!--\s*GG_CMS_SECTORS_TILE_GRID\s*-->#i';
        $patterns[] = '#<!--\s*GG_CMS_SECTEURS_TILE_GRID\s*-->#i';

        foreach ($patterns as $pattern) {
            $html = preg_replace_callback(
                $pattern,
                static function (array $matches) use ($gridHtml, $wideGridHtml): string {
                    $snippet = $matches[0] ?? '';
                    if (preg_match('/data-gg-layout\s*=\s*(?:"wide"|\'wide\'|&quot;wide&quot;)/i', $snippet) === 1) {
                        return $wideGridHtml;
                    }

                    return $gridHtml;
                },
                $html,
            ) ?? $html;
        }

        return cms_apply_structures_html_embeds($html);
    }
}

if (! function_exists('cms_apply_structures_html_embeds')) {
    /**
     * Marqueurs HTML : data-gg-cms="structures-dept-grid" ou commentaire GG_CMS_STRUCTURES_DEPT_GRID.
     */
    function cms_apply_structures_html_embeds(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $gridHtml = cms_structures_render_dept_grid_html();
        if ($gridHtml === '') {
            return $html;
        }

        $patterns = [];
        $q = preg_quote('structures-dept-grid', '~');
        $quote = '(?:"|\'|&quot;|&#34;|&apos;|&#39;)';
        $val = $quote . $q . $quote;
        $in = '(?=[^>]*\bdata-gg-cms\s*=\s*' . $val . ')';
        $inner = '(?:\s|&nbsp;|&#160;|&#x0*A0;|<br\s*/?>)*';
        $patterns[] = '~<div\b' . $in . '[^>]*>' . $inner . '</div>~iu';
        $patterns[] = '~<div\b' . $in . '[^>]*/>~iu';
        $patterns[] = '#<!--\s*GG_CMS_STRUCTURES_DEPT_GRID\s*-->#i';

        $hubHtml = cms_structures_render_hub_html();
        if ($hubHtml !== '') {
            $hq = preg_quote('structures-hub', '~');
            $quote = '(?:"|\'|&quot;|&#34;|&apos;|&#39;)';
            $val = $quote . $hq . $quote;
            $in = '(?=[^>]*\bdata-gg-cms\s*=\s*' . $val . ')';
            $inner = '(?:\s|&nbsp;|&#160;|&#x0*A0;|<br\s*/?>)*';
            $hubPatterns = [
                '~<div\b' . $in . '[^>]*>' . $inner . '</div>~iu',
                '~<div\b' . $in . '[^>]*/>~iu',
                '#<!--\s*GG_CMS_STRUCTURES_HUB\s*-->#i',
                '~(<div class="hub">)\s*<!--\s*GG_CMS_STRUCTURES_HUB\s*-->\s*(</div>)~i',
            ];
            foreach ($hubPatterns as $pattern) {
                $html = preg_replace_callback(
                    $pattern,
                    static function (array $m) use ($hubHtml): string {
                        if (isset($m[1], $m[2]) && str_contains($m[1], 'hub')) {
                            return $m[1] . $hubHtml . $m[2];
                        }

                        return $hubHtml;
                    },
                    $html,
                ) ?? $html;
            }
        }

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
