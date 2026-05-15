<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;

/**
 * Normalisation body_blocks fiche projet depuis POST (admin).
 */
final class ProjectBodyBlocksNormalizer
{
    public static function contentMode(RequestInterface $request): string
    {
        if (! $request instanceof IncomingRequest) {
            return 'html';
        }
        $m = strtolower(trim((string) $request->getPost('body_content_mode')));

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

        return self::encodeBlocksFromPost($request, false);
    }

    /** Pour modérateurs : ignore body_content_mode et rejette les blocs HTML libre. */
    public static function bodyBlocksJsonIgnoringMode(RequestInterface $request): ?string
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        return self::encodeBlocksFromPost($request, true);
    }

    private static function encodeBlocksFromPost(IncomingRequest $request, bool $rejectHtmlBlocks): ?string
    {
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
            $norm = match ($type) {
                'section_rich' => self::normalizeSectionRich($blk),
                'budget_table' => self::normalizeBudgetTable($blk),
                'timeline' => self::normalizeTimeline($blk),
                'kpi_grid' => self::normalizeKpiGrid($blk),
                'note_panel' => self::normalizeNotePanel($blk),
                'impact_tracker' => self::normalizeImpactTracker($blk),
                'team' => self::normalizeTeam($blk),
                'sources' => self::normalizeSources($blk),
                'html' => $rejectHtmlBlocks ? null : self::normalizeHtml($blk),
                default => null,
            };
            if ($norm !== null) {
                $out[] = $norm;
            }
        }

        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeSectionRich(array $blk): ?array
    {
        $heading = trim(strip_tags((string) ($blk['heading'] ?? '')));
        if ($heading === '') {
            return null;
        }
        $style = strtolower(trim((string) ($blk['heading_style'] ?? 'default')));
        if (! in_array($style, ['default', 'warm', 'teal'], true)) {
            $style = 'default';
        }
        $bullets = [];
        $rawB = $blk['bullets'] ?? [];
        if (is_array($rawB)) {
            foreach ($rawB as $line) {
                if (! is_string($line)) {
                    continue;
                }
                $line = trim(strip_tags($line));
                if ($line !== '') {
                    $bullets[] = $line;
                }
            }
        }
        $extras = [];
        $rawE = $blk['extra_paragraphs'] ?? [];
        if (is_array($rawE)) {
            foreach ($rawE as $p) {
                if (! is_string($p)) {
                    continue;
                }
                $p = trim(strip_tags($p));
                if ($p !== '') {
                    $extras[] = $p;
                }
            }
        }

        return [
            'type'               => 'section_rich',
            'heading'            => $heading,
            'heading_style'      => $style,
            'intro'              => trim(strip_tags((string) ($blk['intro'] ?? ''))),
            'bullets'            => $bullets,
            'extra_paragraphs'   => $extras,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeBudgetTable(array $blk): ?array
    {
        $rows = [];
        $raw = $blk['rows'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $poste = trim(strip_tags((string) ($row['poste'] ?? '')));
                $detail = trim(strip_tags((string) ($row['detail'] ?? '')));
                $montant = trim(strip_tags((string) ($row['montant'] ?? '')));
                if ($poste === '' && $detail === '' && $montant === '') {
                    continue;
                }
                $isTotal = strtolower(trim((string) ($row['is_total'] ?? ''))) === '1'
                    || strtolower(trim((string) ($row['is_total'] ?? ''))) === 'yes';
                $rows[] = [
                    'poste'    => $poste,
                    'detail'   => $detail,
                    'montant'  => $montant,
                    'row_class'=> $isTotal ? 'total' : '',
                ];
            }
        }
        if ($rows === []) {
            return null;
        }

        return [
            'type'          => 'budget_table',
            'section_title' => mb_substr(trim(strip_tags((string) ($blk['section_title'] ?? ''))), 0, 255),
            'rows'          => $rows,
            'footnote'      => mb_substr(trim(strip_tags((string) ($blk['footnote'] ?? ''))), 0, 2000),
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeTimeline(array $blk): ?array
    {
        $phases = [];
        $raw = $blk['phases'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $ph) {
                if (! is_array($ph)) {
                    continue;
                }
                $label = mb_substr(trim(strip_tags((string) ($ph['phase_label'] ?? ''))), 0, 120);
                $dur = mb_substr(trim(strip_tags((string) ($ph['duration'] ?? ''))), 0, 120);
                $title = mb_substr(trim(strip_tags((string) ($ph['step_title'] ?? ''))), 0, 255);
                $body = mb_substr(trim(strip_tags((string) ($ph['body'] ?? ''))), 0, 4000);
                if ($label === '' && $dur === '' && $title === '' && $body === '') {
                    continue;
                }
                $phases[] = [
                    'phase_label' => $label,
                    'duration'    => $dur,
                    'step_title'  => $title,
                    'body'        => $body,
                ];
            }
        }
        if ($phases === []) {
            return null;
        }

        return [
            'type'          => 'timeline',
            'section_title' => mb_substr(trim(strip_tags((string) ($blk['section_title'] ?? ''))), 0, 255),
            'phases'        => $phases,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeKpiGrid(array $blk): ?array
    {
        $items = [];
        $raw = $blk['items'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $it) {
                if (! is_array($it)) {
                    continue;
                }
                $v = mb_substr(trim(strip_tags((string) ($it['value'] ?? ''))), 0, 64);
                $l = mb_substr(trim(strip_tags((string) ($it['label'] ?? ''))), 0, 255);
                if ($v === '' && $l === '') {
                    continue;
                }
                $items[] = ['value' => $v, 'label' => $l];
            }
        }
        if ($items === []) {
            return null;
        }
        $style = strtolower(trim((string) ($blk['heading_style'] ?? 'teal')));
        if (! in_array($style, ['default', 'warm', 'teal'], true)) {
            $style = 'teal';
        }

        return [
            'type'            => 'kpi_grid',
            'section_title'   => mb_substr(trim(strip_tags((string) ($blk['section_title'] ?? ''))), 0, 255),
            'heading_style'   => $style,
            'items'           => $items,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeNotePanel(array $blk): ?array
    {
        $message = trim(strip_tags((string) ($blk['message'] ?? '')));
        if ($message === '') {
            return null;
        }
        $style = strtolower(trim((string) ($blk['heading_style'] ?? 'teal')));
        if (! in_array($style, ['default', 'warm', 'teal'], true)) {
            $style = 'teal';
        }

        return [
            'type'            => 'note_panel',
            'section_title'   => mb_substr(trim(strip_tags((string) ($blk['section_title'] ?? ''))), 0, 255),
            'heading_style'   => $style,
            'message'         => mb_substr($message, 0, 2000),
            'submessage'      => mb_substr(trim(strip_tags((string) ($blk['submessage'] ?? ''))), 0, 1000),
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeImpactTracker(array $blk): ?array
    {
        $rows = [];
        $raw = $blk['rows'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $label = mb_substr(trim(strip_tags((string) ($row['label'] ?? ''))), 0, 255);
                $numbers = mb_substr(trim(strip_tags((string) ($row['numbers'] ?? ''))), 0, 255);
                if ($label === '' && $numbers === '') {
                    continue;
                }
                $pct = (int) ($row['bar_percent'] ?? 0);
                if ($pct < 0) {
                    $pct = 0;
                }
                if ($pct > 100) {
                    $pct = 100;
                }
                $rows[] = [
                    'label'       => $label,
                    'numbers'     => $numbers,
                    'bar_percent' => $pct,
                ];
            }
        }
        if ($rows === []) {
            return null;
        }
        $style = strtolower(trim((string) ($blk['heading_style'] ?? 'teal')));
        if (! in_array($style, ['default', 'warm', 'teal'], true)) {
            $style = 'teal';
        }
        $sectionTitle = mb_substr(trim(strip_tags((string) ($blk['section_title'] ?? ''))), 0, 255);
        if ($sectionTitle === '') {
            $sectionTitle = "🎯 Suivi d'impact — Résultats actuels";
        }

        return [
            'type'            => 'impact_tracker',
            'section_title'   => $sectionTitle,
            'heading_style'   => $style,
            'note'            => mb_substr(trim(strip_tags((string) ($blk['note'] ?? ''))), 0, 500),
            'rows'            => $rows,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeTeam(array $blk): ?array
    {
        $members = [];
        $raw = $blk['members'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $m) {
                if (! is_array($m)) {
                    continue;
                }
                $name = mb_substr(trim(strip_tags((string) ($m['name'] ?? ''))), 0, 255);
                $role = mb_substr(trim(strip_tags((string) ($m['role'] ?? ''))), 0, 500);
                if ($name === '' && $role === '') {
                    continue;
                }
                $members[] = ['name' => $name, 'role' => $role];
            }
        }
        if ($members === []) {
            return null;
        }

        return [
            'type'          => 'team',
            'section_title' => mb_substr(trim(strip_tags((string) ($blk['section_title'] ?? ''))), 0, 255),
            'members'       => $members,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeSources(array $blk): ?array
    {
        $lines = [];
        $raw = $blk['lines'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $line) {
                if (! is_string($line)) {
                    continue;
                }
                $line = trim(strip_tags($line));
                if ($line !== '') {
                    $lines[] = mb_substr($line, 0, 500);
                }
            }
        }
        if ($lines === []) {
            return null;
        }

        return [
            'type'          => 'sources',
            'section_title' => mb_substr(trim(strip_tags((string) ($blk['section_title'] ?? ''))), 0, 255),
            'lines'         => $lines,
        ];
    }

    /**
     * @param array<string, mixed> $blk
     *
     * @return array<string, mixed>|null
     */
    private static function normalizeHtml(array $blk): ?array
    {
        $html = trim((string) ($blk['html'] ?? ''));
        if ($html === '') {
            return null;
        }
        if (mb_strlen($html) > 200000) {
            $html = mb_substr($html, 0, 200000);
        }

        return ['type' => 'html', 'html' => $html];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function blocksForForm(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, static fn ($row): bool => is_array($row)));
    }
}
