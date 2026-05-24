<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Rendu HTML des blocs CMS structurés (JSON body_blocks).
 * Contenu éditorial : faire confiance au même niveau que body_html (admins).
 */
final class CmsBodyBlocksRenderer
{
    /**
     * @param list<array<string, mixed>> $blocks
     */
    public static function render(array $blocks): string
    {
        $out = '';
        foreach ($blocks as $b) {
            if (! is_array($b)) {
                continue;
            }
            $out .= match ((string) ($b['type'] ?? '')) {
                'metrics_section' => self::metricsSection($b),
                'html' => (string) ($b['html'] ?? ''),
                default => '',
            };
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function metricsSection(array $b): string
    {
        $kicker   = trim((string) ($b['kicker'] ?? ''));
        $title    = trim((string) ($b['title'] ?? ''));
        $lead     = trim((string) ($b['lead'] ?? ''));
        $footnote = trim((string) ($b['footnote'] ?? ''));

        if ($title === '') {
            return '';
        }

        $hid = trim((string) ($b['heading_id'] ?? ''));
        if ($hid === '') {
            $hid = 'section-' . substr(sha1($title), 0, 10);
        }
        $hid = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $hid);
        if ($hid === '') {
            $hid = 'section-heading';
        }

        $metricsHtml = '';
        $metrics     = $b['metrics'] ?? [];
        if (is_array($metrics)) {
            foreach ($metrics as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $v = trim((string) ($row['value'] ?? ''));
                $l = trim((string) ($row['label'] ?? ''));
                if ($v === '' && $l === '') {
                    continue;
                }
                $metricsHtml .= '<div><dt>' . esc($v) . '</dt><dd>' . esc($l) . '</dd></div>';
            }
        }

        $actionsHtml = '';
        $actions     = $b['actions'] ?? [];
        if (is_array($actions)) {
            foreach ($actions as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $lab = trim((string) ($row['label'] ?? ''));
                $href = trim((string) ($row['href'] ?? ''));
                if ($lab === '' || $href === '') {
                    continue;
                }
                $variant = strtolower(trim((string) ($row['variant'] ?? 'secondary')));
                $variant = $variant === 'primary' ? 'primary' : 'secondary';
                $cls     = $variant === 'primary' ? 'cms-btn cms-btn--primary' : 'cms-btn cms-btn--secondary';
                $actionsHtml .= '<a class="' . esc($cls, 'attr') . '" href="' . esc($href, 'url') . '">' . esc($lab) . '</a> ';
            }
        }

        $html = '<section class="cms-section" aria-labelledby="' . esc($hid, 'attr') . '">';
        $html .= '<header>';
        if ($kicker !== '') {
            $html .= '<p class="cms-kicker">' . esc($kicker) . '</p>';
        }
        $html .= '<h2 id="' . esc($hid, 'attr') . '">' . esc($title) . '</h2>';
        if ($lead !== '') {
            $html .= '<p class="cms-lead muted">' . esc($lead) . '</p>';
        }
        $html .= '</header>';
        if ($metricsHtml !== '') {
            $html .= '<dl class="cms-metrics">' . $metricsHtml . '</dl>';
        }
        if ($footnote !== '') {
            $html .= '<p class="muted">' . esc($footnote) . '</p>';
        }
        if ($actionsHtml !== '') {
            $html .= '<p class="cms-actions">' . trim($actionsHtml) . '</p>';
        }
        $html .= '</section>';

        return $html;
    }
}
