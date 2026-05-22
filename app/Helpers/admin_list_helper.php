<?php

declare(strict_types=1);

if (! function_exists('admin_list_sort_url')) {
    /**
     * URL de liste admin en conservant filtres GET ; bascule asc/desc sur la colonne active.
     */
    function admin_list_sort_url(string $column, string $currentSort, string $currentDir): string
    {
        helper('url');

        $params = service('request')->getGet();
        if (! is_array($params)) {
            $params = [];
        }

        if ($currentSort === $column) {
            $params['dir'] = strtolower($currentDir) === 'asc' ? 'desc' : 'asc';
        } else {
            $params['sort'] = $column;
            $params['dir']  = 'asc';
        }

        unset($params['page']);

        $path = ltrim((string) service('request')->getUri()->getPath(), '/');
        $base = site_url($path);
        $qs   = http_build_query($params);

        return $qs !== '' ? $base . '?' . $qs : $base;
    }
}

if (! function_exists('admin_list_sort_th')) {
    /** En-tête de colonne triable pour les tableaux admin. */
    function admin_list_sort_th(string $column, string $label, string $sort, string $dir): string
    {
        $active = $sort === $column;
        $arrow  = '';
        if ($active) {
            $glyph = strtolower($dir) === 'asc' ? '▲' : '▼';
            $arrow = ' <span class="admin-sort-arrow" aria-hidden="true">' . $glyph . '</span>';
        }

        $url = admin_list_sort_url($column, $sort, $dir);
        $cls = 'admin-sort-link text-decoration-none';
        if ($active) {
            $cls .= ' admin-sort-link--active fw-semibold';
        }

        $aria = $active
            ? ' aria-sort="' . (strtolower($dir) === 'asc' ? 'ascending' : 'descending') . '"'
            : '';

        return '<a href="' . esc($url, 'attr') . '" class="' . esc($cls, 'attr') . '"' . $aria . '>'
            . esc($label) . $arrow . '</a>';
    }
}

if (! function_exists('admin_list_sort_hidden_fields')) {
    /** Conserve le tri lors d’un formulaire GET de filtre. */
    function admin_list_sort_hidden_fields(string $sort, string $dir): string
    {
        return '<input type="hidden" name="sort" value="' . esc($sort, 'attr') . '">'
            . '<input type="hidden" name="dir" value="' . esc(strtolower($dir), 'attr') . '">';
    }
}
