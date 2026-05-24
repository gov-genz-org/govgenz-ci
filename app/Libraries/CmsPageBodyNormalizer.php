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
            if ($type === 'metrics_section') {
                $norm = self::normalizeMetricsSectionBlock($blk);
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
        $title = trim((string) ($blk['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        $metrics = [];
        $rawM    = $blk['metrics'] ?? [];
        if (is_array($rawM)) {
            foreach ($rawM as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $v = trim((string) ($row['value'] ?? ''));
                $l = trim((string) ($row['label'] ?? ''));
                if ($v === '' && $l === '') {
                    continue;
                }
                $metrics[] = ['value' => $v, 'label' => $l];
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
            'type'       => 'metrics_section',
            'kicker'     => trim((string) ($blk['kicker'] ?? '')),
            'title'      => $title,
            'heading_id' => trim((string) ($blk['heading_id'] ?? '')),
            'lead'       => trim((string) ($blk['lead'] ?? '')),
            'footnote'   => trim((string) ($blk['footnote'] ?? '')),
            'metrics'    => $metrics,
            'actions'    => $actions,
        ];
    }
}
