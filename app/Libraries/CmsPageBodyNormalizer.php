<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;

/**
 * Normalisation du corps de page CMS depuis une requête POST (formulaire admin).
 */
final class CmsPageBodyNormalizer
{
    public static function contentMode(RequestInterface $request): string
    {
        if (! $request instanceof IncomingRequest) {
            return 'html';
        }

        $m = strtolower(trim((string) $request->getPost('content_mode')));

        return $m === 'blocks' ? 'blocks' : 'html';
    }

    public static function bodyBlocksJson(RequestInterface $request): ?string
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        if (self::contentMode($request) !== 'blocks') {
            return null;
        }

        $raw = $request->getPost('blocks');
        if (! is_array($raw)) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        $out = [];
        foreach ($raw as $blk) {
            if (! is_array($blk)) {
                continue;
            }
            $type = (string) ($blk['type'] ?? '');
            if ($type === 'metrics_section' || $type === 'stats_grid') {
                $norm = self::normalizeStatsGridBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'section_text') {
                $norm = self::normalizeSectionTextBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'cards_grid') {
                $norm = self::normalizeCardsGridBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'organization_hub') {
                $norm = self::normalizeOrganizationHubBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'contact_grid') {
                $norm = self::normalizeContactGridBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'cta_panel') {
                $norm = self::normalizeCtaPanelBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'legal_prose') {
                $norm = self::normalizeLegalProseBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'sources') {
                $norm = self::normalizeSourcesBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'sectors_grid') {
                $norm = self::normalizeSectionHeader($blk);
                $norm['type'] = 'sectors_grid';
                $out[] = $norm;
            } elseif ($type === 'footer_columns') {
                $norm = self::normalizeFooterColumnsBlock($blk);
                if ($norm !== null) {
                    $out[] = $norm;
                }
            } elseif ($type === 'html') {
                $html = trim((string) ($blk['html'] ?? ''));
                if ($html !== '') {
                    $out[] = ['type' => 'html', 'html' => $html];
                }
            }
        }

        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    public static function normalizeMetricsSectionBlock(array $blk): ?array
    {
        return self::normalizeStatsGridBlock($blk);
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    public static function normalizeStatsGridBlock(array $blk): ?array
    {
        $title = trim((string) ($blk['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        $stats = [];
        $rawM  = $blk['stats'] ?? ($blk['metrics'] ?? []);
        if (is_array($rawM)) {
            foreach ($rawM as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $v = trim((string) ($row['value'] ?? ''));
                $l = trim((string) ($row['label'] ?? ''));
                $suffix = trim((string) ($row['suffix'] ?? ''));
                if ($v === '' && $suffix === '' && $l === '') {
                    continue;
                }
                $stats[] = ['value' => $v, 'suffix' => $suffix, 'label' => $l];
            }
        }

        $actions = [];
        $rawA    = $blk['actions'] ?? [];
        if (is_array($rawA)) {
            foreach ($rawA as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $lab = trim((string) ($row['label'] ?? ''));
                $href = trim((string) ($row['href'] ?? ''));
                if ($lab === '' || $href === '') {
                    continue;
                }
                $var = strtolower(trim((string) ($row['variant'] ?? 'secondary')));
                $actions[] = [
                    'label'   => $lab,
                    'href'    => $href,
                    'variant' => $var === 'primary' ? 'primary' : 'secondary',
                ];
            }
        }

        return [
            'type'       => 'stats_grid',
            'kicker'     => trim((string) ($blk['kicker'] ?? '')),
            'title'      => $title,
            'heading_id' => trim((string) ($blk['heading_id'] ?? '')),
            'lead'       => trim((string) ($blk['lead'] ?? '')),
            'footnote'   => trim((string) ($blk['footnote'] ?? '')),
            'stats'      => $stats,
            'actions'    => $actions,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, string>
     */
    private static function normalizeSectionHeader(array $blk): array
    {
        return [
            'kicker'     => trim((string) ($blk['kicker'] ?? '')),
            'title'      => trim((string) ($blk['title'] ?? '')),
            'heading_id' => trim((string) ($blk['heading_id'] ?? '')),
            'lead'       => trim((string) ($blk['lead'] ?? '')),
        ];
    }

    /**
     * @param mixed $raw
     *
     * @return list<string>
     */
    private static function normalizeScalarLines(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = preg_split('/\R/u', $raw) ?: [];
        }
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $out[] = $line;
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeSectionTextBlock(array $blk): ?array
    {
        $data = self::normalizeSectionHeader($blk);
        $paragraphs = self::normalizeScalarLines($blk['paragraphs'] ?? []);
        $bullets = self::normalizeScalarLines($blk['bullets'] ?? []);
        $source = trim((string) ($blk['source'] ?? ''));
        if ($data['title'] === '' && $data['lead'] === '' && $paragraphs === [] && $bullets === [] && $source === '') {
            return null;
        }

        return $data + [
            'type'       => 'section_text',
            'paragraphs' => $paragraphs,
            'bullets'    => $bullets,
            'source'     => $source,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeCardsGridBlock(array $blk): ?array
    {
        $data = self::normalizeSectionHeader($blk);
        $variant = strtolower(trim((string) ($blk['variant'] ?? 'simple_cards')));
        if (! in_array($variant, ['simple_cards', 'circle_cards', 'pillar_cards', 'tile_grid'], true)) {
            $variant = 'simple_cards';
        }

        $cards = [];
        $raw = $blk['cards'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $mediaId = self::normalizeMediaId($row['media_id'] ?? null);
                $mediaId2 = self::normalizeMediaId($row['media_id_2'] ?? null);
                $card = [
                    'eyebrow'     => trim((string) ($row['eyebrow'] ?? '')),
                    'value'       => trim((string) ($row['value'] ?? '')),
                    'unit'        => trim((string) ($row['unit'] ?? '')),
                    'title'       => trim((string) ($row['title'] ?? '')),
                    'subtitle'    => trim((string) ($row['subtitle'] ?? '')),
                    'description' => trim((string) ($row['description'] ?? '')),
                    'href'        => trim((string) ($row['href'] ?? '')),
                    'media_id'    => $mediaId,
                    'media_alt'   => $mediaId > 0 ? trim((string) ($row['media_alt'] ?? '')) : '',
                    'media_id_2'  => $mediaId2,
                    'media_alt_2' => $mediaId2 > 0 ? trim((string) ($row['media_alt_2'] ?? '')) : '',
                    'icon_url'    => trim((string) ($row['icon_url'] ?? '')),
                    'bullets'     => self::normalizeScalarLines($row['bullets_text'] ?? ($row['bullets'] ?? [])),
                ];
                if (implode('', array_map(static fn ($v): string => is_array($v) ? implode('', $v) : (string) $v, $card)) === '') {
                    continue;
                }
                $cards[] = $card;
            }
        }

        if ($data['title'] === '' && $data['lead'] === '' && $cards === []) {
            return null;
        }

        return $data + [
            'type'    => 'cards_grid',
            'variant' => $variant,
            'source'  => trim((string) ($blk['source'] ?? '')),
            'cards'   => $cards,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeOrganizationHubBlock(array $blk): ?array
    {
        $data = self::normalizeSectionHeader($blk);
        $items = [];
        $raw = $blk['items'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $name = trim((string) ($row['name'] ?? ''));
                $subtitle = trim((string) ($row['subtitle'] ?? ''));
                $href = trim((string) ($row['href'] ?? ''));
                if ($name === '' && $subtitle === '' && $href === '') {
                    continue;
                }
                $items[] = ['name' => $name, 'subtitle' => $subtitle, 'href' => $href];
            }
        }
        $coreLabel = trim((string) ($blk['core_label'] ?? ''));
        $coreSubtitle = trim((string) ($blk['core_subtitle'] ?? ''));
        $coreHref = trim((string) ($blk['core_href'] ?? ''));
        if ($data['title'] === '' && $data['lead'] === '' && $coreLabel === '' && $coreSubtitle === '' && $coreHref === '' && $items === []) {
            return null;
        }

        return $data + [
            'type'          => 'organization_hub',
            'core_label'    => $coreLabel,
            'core_subtitle' => $coreSubtitle,
            'core_href'     => $coreHref,
            'items'         => $items,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeContactGridBlock(array $blk): ?array
    {
        $data = self::normalizeSectionHeader($blk);
        $items = [];
        $raw = $blk['items'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $item = [
                    'label'    => trim((string) ($row['label'] ?? '')),
                    'title'    => trim((string) ($row['title'] ?? '')),
                    'subtitle' => trim((string) ($row['subtitle'] ?? '')),
                    'href'     => trim((string) ($row['href'] ?? '')),
                ];
                if (implode('', $item) === '') {
                    continue;
                }
                $items[] = $item;
            }
        }
        if ($data['title'] === '' && $data['lead'] === '' && $items === []) {
            return null;
        }

        return $data + ['type' => 'contact_grid', 'items' => $items];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeFooterColumnsBlock(array $blk): ?array
    {
        $columns = [];
        $raw = $blk['columns'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $column) {
                if (! is_array($column)) {
                    continue;
                }
                $title = trim((string) ($column['title'] ?? ''));
                $links = [];
                $rawLinks = $column['links'] ?? [];
                if (is_array($rawLinks)) {
                    foreach ($rawLinks as $row) {
                        if (! is_array($row)) {
                            continue;
                        }
                        $label = trim((string) ($row['label'] ?? ''));
                        if ($label === '') {
                            continue;
                        }
                        $soon = (int) ($row['soon'] ?? 0) === 1;
                        $links[] = [
                            'label' => $label,
                            'href'  => $soon ? '' : trim((string) ($row['href'] ?? '')),
                            'soon'  => $soon ? 1 : 0,
                        ];
                    }
                }
                if ($title === '' && $links === []) {
                    continue;
                }
                $columns[] = [
                    'title' => $title,
                    'links' => $links,
                ];
            }
        }
        if ($columns === []) {
            return null;
        }

        return ['type' => 'footer_columns', 'columns' => $columns];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeCtaPanelBlock(array $blk): ?array
    {
        $text = trim((string) ($blk['text'] ?? ''));
        $actions = [];
        $raw = $blk['actions'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $label = trim((string) ($row['label'] ?? ''));
                $href = trim((string) ($row['href'] ?? ''));
                if ($label === '' || $href === '') {
                    continue;
                }
                $variant = strtolower(trim((string) ($row['variant'] ?? 'primary')));
                $actions[] = ['label' => $label, 'href' => $href, 'variant' => $variant === 'secondary' ? 'secondary' : 'primary'];
            }
        }
        if ($text === '' && $actions === []) {
            return null;
        }

        return ['type' => 'cta_panel', 'text' => $text, 'actions' => $actions];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeLegalProseBlock(array $blk): ?array
    {
        $sections = [];
        $raw = $blk['sections'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $heading = trim((string) ($row['heading'] ?? ''));
                $body = trim((string) ($row['body'] ?? ''));
                $bullets = self::normalizeScalarLines($row['bullets_text'] ?? ($row['bullets'] ?? []));
                if ($heading === '' && $body === '' && $bullets === []) {
                    continue;
                }
                $sections[] = ['heading' => $heading, 'body' => $body, 'bullets' => $bullets];
            }
        }

        return $sections === [] ? null : ['type' => 'legal_prose', 'sections' => $sections];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeSourcesBlock(array $blk): ?array
    {
        $lines = self::normalizeScalarLines($blk['lines'] ?? []);

        return $lines === [] ? null : ['type' => 'sources', 'lines' => $lines];
    }

    private static function normalizeMediaId(mixed $rawId): int
    {
        if ($rawId === null || $rawId === '') {
            return 0;
        }

        $id = (int) $rawId;
        if ($id <= 0) {
            return 0;
        }

        $row = model(\App\Models\CmsMediaModel::class)->find($id);
        if ($row === null) {
            return 0;
        }

        $fn = trim((string) ($row['stored_filename'] ?? ''));
        if ($fn === '') {
            return 0;
        }

        return $id;
    }
}
