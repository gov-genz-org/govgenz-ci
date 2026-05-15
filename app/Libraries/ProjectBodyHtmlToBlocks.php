<?php

declare(strict_types=1);

namespace App\Libraries;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Convertit le HTML project-main (seed / legacy TinyMCE) en body_blocks JSON.
 */
final class ProjectBodyHtmlToBlocks
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function parse(string $html): array
    {
        $inner = self::extractProjectMainInner($html);
        if ($inner === '') {
            return [];
        }

        $sections = self::splitContentSections($inner);
        if ($sections === []) {
            return [['type' => 'html', 'html' => '<div class="project-main">' . $inner . '</div>']];
        }

        $blocks = [];
        foreach ($sections as $sectionHtml) {
            $block = self::parseContentSection($sectionHtml);
            if ($block !== null) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    public static function toJson(string $html): string
    {
        $encoded = json_encode(self::parse($html), JSON_UNESCAPED_UNICODE);

        return is_string($encoded) ? $encoded : '[]';
    }


    /**
     * Remplace les blocs html legacy contenant un suivi d'impact par impact_tracker.
     *
     * @param list<array<string, mixed>> $blocks
     *
     * @return list<array<string, mixed>>
     */
    public static function upgradeBlocksList(array $blocks): array
    {
        $out = [];
        foreach ($blocks as $b) {
            if (! is_array($b)) {
                continue;
            }
            if (($b['type'] ?? '') === 'html' && str_contains((string) ($b['html'] ?? ''), 'impact-tracker')) {
                $parsed = self::parseContentSection((string) $b['html']);
                if ($parsed !== null && ($parsed['type'] ?? '') === 'impact_tracker') {
                    $out[] = $parsed;

                    continue;
                }
            }
            $out[] = $b;
        }

        return $out;
    }


    public static function extractProjectMainInner(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (preg_match('#<div\s+class=["\']project-main["\'][^>]*>(.*)</div>\s*(?:<!--|$)#is', $html, $m) === 1) {
            return trim($m[1]);
        }

        if (preg_match('#<div\s+class=["\']project-main["\'][^>]*>(.*)#is', $html, $m) === 1) {
            return trim($m[1]);
        }

        return $html;
    }

    /**
     * @return list<string>
     */
    private static function splitContentSections(string $inner): array
    {
        $doc = self::loadHtml('<div id="pp-root">' . $inner . '</div>');
        if ($doc === null) {
            return [];
        }

        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query("//*[@id='pp-root']/div[contains(concat(' ', normalize-space(@class), ' '), ' content-section ')]");

        if ($nodes === false || $nodes->length === 0) {
            return [];
        }

        $out = [];
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $out[] = self::outerHtml($node);
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseContentSection(string $sectionHtml): ?array
    {
        $doc = self::loadHtml($sectionHtml);
        if ($doc === null) {
            return self::htmlBlock($sectionHtml);
        }

        $xpath = new DOMXPath($doc);
        $section = $xpath->query("//*[@id='pp-wrap']/div[contains(concat(' ', normalize-space(@class), ' '), ' content-section ')]")->item(0);
        if (! $section instanceof DOMElement) {
            return self::htmlBlock($sectionHtml);
        }

        if ($xpath->query('.//table[contains(@class,"budget-table")]', $section)->length > 0) {
            return self::parseBudgetSection($section, $xpath);
        }
        if ($xpath->query('.//div[contains(@class,"phases")]', $section)->length > 0) {
            return self::parseTimelineSection($section, $xpath);
        }
        if ($xpath->query('.//div[contains(@class,"kpis-grid")]', $section)->length > 0) {
            return self::parseKpiSection($section, $xpath);
        }
        if ($xpath->query('.//div[contains(@class,"team-list")]', $section)->length > 0) {
            return self::parseTeamSection($section, $xpath);
        }
        if ($xpath->query('.//div[contains(@class,"impact-tracker")]', $section)->length > 0) {
            $impact = self::parseImpactTrackerSection($section, $xpath);
            if ($impact !== null) {
                return $impact;
            }
        }
        if ($xpath->query('.//*[contains(@style,"dashed")]', $section)->length > 0) {
            $note = self::parseNotePanelSection($section, $xpath);
            if ($note !== null) {
                return $note;
            }
        }

        $titleNode = $xpath->query('.//div[contains(@class,"content-title")]', $section)->item(0);
        if ($titleNode instanceof DOMElement) {
            $titleText = self::textContent($titleNode);
            if (self::looksLikeSourcesSection($titleText) && $xpath->query('.//ul', $section)->length > 0) {
                return self::parseSourcesSection($section, $xpath, $titleText);
            }

            return self::parseSectionRich($section, $xpath, $titleNode);
        }

        return self::htmlBlock($sectionHtml);
    }

    /**
     * @return array<string, mixed>
     */
    private static function parseSectionRich(DOMElement $section, DOMXPath $xpath, DOMElement $titleNode): array
    {
        $heading = self::textContent($titleNode);
        $class   = $titleNode->getAttribute('class');
        $style   = 'default';
        if (str_contains($class, 'warm')) {
            $style = 'warm';
        } elseif (str_contains($class, 'teal')) {
            $style = 'teal';
        }

        $intro = '';
        $bullets = [];
        $extras = [];
        $pastTitle = false;
        $pastFirstList = false;

        foreach ($section->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            if ($child->isSameNode($titleNode)) {
                $pastTitle = true;

                continue;
            }
            if (! $pastTitle) {
                continue;
            }

            if ($child->tagName === 'p' && ! $pastFirstList) {
                $intro = self::textContent($child);

                continue;
            }
            if ($child->tagName === 'ul') {
                $pastFirstList = true;
                foreach ($child->getElementsByTagName('li') as $li) {
                    $line = self::textContent($li);
                    if ($line !== '') {
                        $bullets[] = $line;
                    }
                }

                continue;
            }
            if ($child->tagName === 'p') {
                $t = self::textContent($child);
                if ($t !== '') {
                    $extras[] = $t;
                }
            }
        }

        return [
            'type'             => 'section_rich',
            'heading'          => $heading,
            'heading_style'    => $style,
            'intro'            => $intro,
            'bullets'          => $bullets,
            'extra_paragraphs' => $extras,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseBudgetSection(DOMElement $section, DOMXPath $xpath): ?array
    {
        $titleNode = $xpath->query('.//div[contains(@class,"content-title")]', $section)->item(0);
        $sectionTitle = $titleNode instanceof DOMElement ? self::textContent($titleNode) : '💰 Budget détaillé';

        $rows = [];
        $trNodes = $xpath->query('.//table[contains(@class,"budget-table")]//tbody//tr', $section);
        if ($trNodes !== false) {
            foreach ($trNodes as $tr) {
                if (! $tr instanceof DOMElement) {
                    continue;
                }
                $cells = [];
                foreach ($tr->getElementsByTagName('td') as $td) {
                    $cells[] = self::textContent($td);
                }
                if ($cells === []) {
                    continue;
                }
                $isTotal = str_contains($tr->getAttribute('class'), 'budget-total');
                $poste = $cells[0] ?? '';
                $detail = $cells[1] ?? '';
                $montant = $cells[2] ?? '';
                if (count($cells) === 2) {
                    $montant = $cells[1];
                    $detail  = '';
                }
                if ($poste === '' && $detail === '' && $montant === '') {
                    continue;
                }
                $rows[] = [
                    'poste'     => $poste,
                    'detail'    => $detail,
                    'montant'   => $montant,
                    'row_class' => $isTotal ? 'total' : '',
                ];
            }
        }

        if ($rows === []) {
            return null;
        }

        $footnote = '';
        foreach ($section->getElementsByTagName('p') as $p) {
            $t = self::textContent($p);
            if ($t !== '') {
                $footnote = $t;
            }
        }

        return [
            'type'          => 'budget_table',
            'section_title' => $sectionTitle,
            'rows'          => $rows,
            'footnote'      => $footnote,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseTimelineSection(DOMElement $section, DOMXPath $xpath): ?array
    {
        $titleNode = $xpath->query('.//div[contains(@class,"content-title")]', $section)->item(0);
        $sectionTitle = $titleNode instanceof DOMElement ? self::textContent($titleNode) : '📅 Calendrier';

        $phases = [];
        $phaseNodes = $xpath->query(".//div[contains(concat(' ', normalize-space(@class), ' '), ' phase ')]", $section);
        if ($phaseNodes !== false) {
            foreach ($phaseNodes as $ph) {
                if (! $ph instanceof DOMElement) {
                    continue;
                }
                $label = '';
                $duration = '';
                $stepTitle = '';
                $body = '';

                $numNode = $xpath->query('.//div[contains(@class,"phase-num")]', $ph)->item(0);
                if ($numNode instanceof DOMElement) {
                    $durNode = $xpath->query('.//span[contains(@class,"phase-duration")]', $numNode)->item(0);
                    if ($durNode instanceof DOMElement) {
                        $duration = self::textContent($durNode);
                    }
                    $label = self::textContent($numNode);
                    if ($duration !== '' && str_ends_with($label, $duration)) {
                        $label = trim(substr($label, 0, -strlen($duration)));
                    }
                }

                $h5 = $ph->getElementsByTagName('h5')->item(0);
                if ($h5 instanceof DOMElement) {
                    $stepTitle = self::textContent($h5);
                }
                foreach ($ph->getElementsByTagName('p') as $p) {
                    $body = self::textContent($p);
                }

                if ($label === '' && $duration === '' && $stepTitle === '' && $body === '') {
                    continue;
                }

                $phases[] = [
                    'phase_label' => $label,
                    'duration'    => $duration,
                    'step_title'  => $stepTitle,
                    'body'        => $body,
                ];
            }
        }

        if ($phases === []) {
            return null;
        }

        return [
            'type'          => 'timeline',
            'section_title' => $sectionTitle,
            'phases'        => $phases,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseKpiSection(DOMElement $section, DOMXPath $xpath): ?array
    {
        $titleNode = $xpath->query('.//div[contains(@class,"content-title")]', $section)->item(0);
        if (! $titleNode instanceof DOMElement) {
            return null;
        }

        $sectionTitle = self::textContent($titleNode);
        $class        = $titleNode->getAttribute('class');
        $style        = 'teal';
        if (str_contains($class, 'warm')) {
            $style = 'warm';
        } elseif (! str_contains($class, 'teal')) {
            $style = 'default';
        }

        $items = [];
        $cards = $xpath->query('.//div[contains(@class,"kpi-card")]', $section);
        if ($cards !== false) {
            foreach ($cards as $card) {
                if (! $card instanceof DOMElement) {
                    continue;
                }
                $valNode = $xpath->query('.//div[contains(@class,"kpi-n")]', $card)->item(0);
                $labNode = $xpath->query('.//div[contains(@class,"kpi-l")]', $card)->item(0);
                $value = $valNode instanceof DOMElement ? self::textContent($valNode) : '';
                $label = $labNode instanceof DOMElement ? self::textContent($labNode) : '';
                if ($value === '' && $label === '') {
                    continue;
                }
                $items[] = ['value' => $value, 'label' => $label];
            }
        }

        if ($items === []) {
            return null;
        }

        return [
            'type'          => 'kpi_grid',
            'section_title' => $sectionTitle,
            'heading_style' => $style,
            'items'         => $items,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseImpactTrackerSection(DOMElement $section, DOMXPath $xpath): ?array
    {
        $titleNode = $xpath->query('.//div[contains(@class,"content-title")]', $section)->item(0);
        $sectionTitle = $titleNode instanceof DOMElement ? self::textContent($titleNode) : "🎯 Suivi d'impact — Résultats actuels";
        $style        = 'teal';
        if ($titleNode instanceof DOMElement) {
            $class = $titleNode->getAttribute('class');
            if (str_contains($class, 'warm')) {
                $style = 'warm';
            } elseif (! str_contains($class, 'teal')) {
                $style = 'default';
            }
        }

        $note = '';
        $noteNode = $xpath->query('.//p[contains(@class,"impact-note")]', $section)->item(0);
        if ($noteNode instanceof DOMElement) {
            $note = self::textContent($noteNode);
        }

        $rows    = [];
        $tracker = $xpath->query('.//div[contains(@class,"impact-tracker")]', $section)->item(0);
        if ($tracker instanceof DOMElement) {
            foreach ($tracker->childNodes as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }
                $labelNode   = $xpath->query('.//*[contains(@class,"impact-label")]', $child)->item(0);
                $numbersNode = $xpath->query('.//*[contains(@class,"impact-numbers")]', $child)->item(0);
                $label       = $labelNode instanceof DOMElement ? self::textContent($labelNode) : '';
                $numbers     = $numbersNode instanceof DOMElement ? self::textContent($numbersNode) : '';
                $fill        = $xpath->query('.//*[contains(@class,"impact-bar-fill")]', $child)->item(0);
                $pct         = 0;
                if ($fill instanceof DOMElement) {
                    $barStyle = $fill->getAttribute('style');
                    if (preg_match('/width:\s*(\d+(?:\.\d+)?)\s*%/', $barStyle, $m) === 1) {
                        $pct = (int) round((float) $m[1]);
                    }
                }
                if ($label === '' && $numbers === '') {
                    continue;
                }
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

        return [
            'type'          => 'impact_tracker',
            'section_title' => $sectionTitle,
            'heading_style' => $style,
            'note'          => $note,
            'rows'          => $rows,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseTeamSection(DOMElement $section, DOMXPath $xpath): ?array
    {
        $titleNode = $xpath->query('.//div[contains(@class,"content-title")]', $section)->item(0);
        $sectionTitle = $titleNode instanceof DOMElement ? self::textContent($titleNode) : '👥 Équipe projet';

        $members = [];
        $rows = $xpath->query('.//div[contains(@class,"team-row")]', $section);
        if ($rows !== false) {
            foreach ($rows as $row) {
                if (! $row instanceof DOMElement) {
                    continue;
                }
                $strong = $row->getElementsByTagName('strong')->item(0);
                $small  = $row->getElementsByTagName('small')->item(0);
                $name   = $strong instanceof DOMElement ? self::textContent($strong) : '';
                $role   = $small instanceof DOMElement ? self::textContent($small) : '';
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
            'section_title' => $sectionTitle,
            'members'       => $members,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseSourcesSection(DOMElement $section, DOMXPath $xpath, string $sectionTitle): ?array
    {
        $lines = [];
        $lis = $xpath->query('.//ul//li', $section);
        if ($lis !== false) {
            foreach ($lis as $li) {
                if (! $li instanceof DOMElement) {
                    continue;
                }
                $line = self::textContent($li);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }
        }

        if ($lines === []) {
            return null;
        }

        return [
            'type'          => 'sources',
            'section_title' => $sectionTitle,
            'lines'         => $lines,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseNotePanelSection(DOMElement $section, DOMXPath $xpath): ?array
    {
        $titleNode = $xpath->query('.//div[contains(@class,"content-title")]', $section)->item(0);

        $sectionTitle = $titleNode instanceof DOMElement ? self::textContent($titleNode) : '';
        $style        = 'teal';
        if ($titleNode instanceof DOMElement) {
            $class = $titleNode->getAttribute('class');
            if (str_contains($class, 'warm')) {
                $style = 'warm';
            } elseif (! str_contains($class, 'teal')) {
                $style = 'default';
            }
        }

        $dashed = $xpath->query('.//*[contains(@style,"dashed")]', $section)->item(0);
        if (! $dashed instanceof DOMElement) {
            return null;
        }

        $message = self::textContent($dashed);
        if ($message === '') {
            return null;
        }

        return [
            'type'          => 'note_panel',
            'section_title' => $sectionTitle,
            'heading_style' => $style,
            'message'       => $message,
            'submessage'    => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function htmlBlock(string $html): array
    {
        return ['type' => 'html', 'html' => trim($html)];
    }

    private static function looksLikeSourcesSection(string $title): bool
    {
        $t = mb_strtolower($title);

        return str_contains($t, 'source') || str_contains($t, 'document') || str_contains($title, '📎');
    }

    private static function textContent(DOMNode $node): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', html_entity_decode($node->textContent ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

        return $t;
    }

    private static function outerHtml(DOMElement $node): string
    {
        $doc = $node->ownerDocument;
        if ($doc === null) {
            return '';
        }

        return (string) $doc->saveHTML($node);
    }

    private static function loadHtml(string $fragment): ?DOMDocument
    {
        $prev = libxml_use_internal_errors(true);
        $doc  = new DOMDocument('1.0', 'UTF-8');
        $wrap = '<?xml encoding="UTF-8"><div id="pp-wrap">' . $fragment . '</div>';
        if (! $doc->loadHTML($wrap, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);

            return null;
        }
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        return $doc;
    }
}
