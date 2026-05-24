<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Rendu HTML des blocs fiche projet (JSON body_blocks) — classes alignées sur site_govgenz/projects-govgenz.
 */
final class ProjectBodyBlocksRenderer
{
    /**
     * @param list<array<string, mixed>> $blocks
     */
    public static function render(array $blocks, string $locale = 'fr'): string
    {
        if (! in_array($locale, ['fr', 'en'], true)) {
            $locale = 'fr';
        }

        $inner = '';
        foreach ($blocks as $b) {
            if (! is_array($b)) {
                continue;
            }
            $inner .= match ((string) ($b['type'] ?? '')) {
                'section_rich' => self::sectionRich($b),
                'budget_table' => self::budgetTable($b, $locale),
                'material_needs' => self::materialNeeds($b, $locale),
                'timeline' => self::timeline($b),
                'kpi_grid' => self::kpiGrid($b),
                'note_panel' => self::notePanel($b),
                'impact_tracker' => self::impactTracker($b),
                'team' => self::team($b),
                'sources' => self::sources($b),
                'html' => (string) ($b['html'] ?? ''),
                default => '',
            };
        }

        return '<div class="project-main">' . $inner . '</div>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function sectionRich(array $b): string
    {
        $heading = trim((string) ($b['heading'] ?? ''));
        if ($heading === '') {
            return '';
        }
        $style = strtolower(trim((string) ($b['heading_style'] ?? 'default')));
        $titleClass = 'content-title';
        if ($style === 'warm') {
            $titleClass .= ' warm';
        } elseif ($style === 'teal') {
            $titleClass .= ' teal';
        }

        $html = '<div class="content-section">';
        $html .= '<div class="' . esc($titleClass, 'attr') . '">' . esc($heading) . '</div>';
        $intro = trim((string) ($b['intro'] ?? ''));
        if ($intro !== '') {
            $html .= '<p>' . nl2br(esc($intro), false) . '</p>';
        }
        $bullets = $b['bullets'] ?? [];
        if (is_array($bullets) && $bullets !== []) {
            helper('admin');
            $html .= '<ul>';
            foreach ($bullets as $line) {
                if (! is_string($line)) {
                    continue;
                }
                $line = trim($line);
                if ($line === '' || admin_pp_is_junk_repeat_line($line)) {
                    continue;
                }
                $html .= '<li>' . esc($line) . '</li>';
            }
            $html .= '</ul>';
        }
        $extras = $b['extra_paragraphs'] ?? [];
        if (is_array($extras)) {
            foreach ($extras as $p) {
                if (! is_string($p)) {
                    continue;
                }
                $p = trim($p);
                if ($p === '') {
                    continue;
                }
                $html .= '<p>' . nl2br(esc($p), false) . '</p>';
            }
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function budgetTable(array $b, string $locale): string
    {
        helper(['project', 'language', 'admin']);

        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = '💰 Budget détaillé';
        }
        $rows = $b['rows'] ?? [];
        if (! is_array($rows) || $rows === []) {
            return '';
        }

        $sumAriary       = project_budget_table_sum_ariary($rows);
        $totalRowLabel   = '';
        $bodyRows        = '';

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (ProjectBudgetTableSync::rowIsTotal($row)) {
                $poste = trim((string) ($row['poste'] ?? ''));
                if ($poste !== '') {
                    $totalRowLabel = $poste;
                }
                continue;
            }

            $poste   = admin_pp_scrub_junk_text(trim((string) ($row['poste'] ?? '')));
            $detail  = admin_pp_scrub_junk_text(trim((string) ($row['detail'] ?? '')));
            $montant = admin_pp_scrub_junk_text(trim((string) ($row['montant'] ?? '')));
            if ($poste === '' && $detail === '' && $montant === '') {
                continue;
            }

            $bodyRows .= '<tr>';
            $bodyRows .= '<td>' . esc($poste) . '</td><td>' . esc($detail) . '</td><td>' . esc($montant) . '</td>';
            $bodyRows .= '</tr>';
        }

        if ($sumAriary !== null && $sumAriary > 0) {
            $label = $totalRowLabel !== '' ? $totalRowLabel : (string) lang('Projects.budget_table_total_row');
            $sumDisplay = project_format_ariary_table_cell($sumAriary, $locale);
            $bodyRows .= '<tr class="budget-total">';
            $bodyRows .= '<td colspan="2"><strong>' . esc($label) . '</strong></td>';
            $bodyRows .= '<td><strong>' . esc($sumDisplay) . '</strong></td>';
            $bodyRows .= '</tr>';
        }

        if ($bodyRows === '') {
            return '';
        }
        $footnote = trim((string) ($b['footnote'] ?? ''));
        $html = '<div class="content-section">';
        $html .= '<div class="content-title">' . esc($sectionTitle) . '</div>';
        $html .= '<table class="budget-table"><thead><tr><th>Poste</th><th>Détail</th><th>Montant (Ar)</th></tr></thead><tbody>';
        $html .= $bodyRows . '</tbody></table>';
        if ($footnote !== '') {
            $html .= '<p class="small text-muted mt-2 mb-0">' . nl2br(esc($footnote), false) . '</p>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function materialNeeds(array $b, string $locale): string
    {
        unset($locale);

        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = (string) lang('Projects.material_needs_default_title');
        }
        $rows = $b['rows'] ?? [];
        if (! is_array($rows) || $rows === []) {
            return '';
        }

        $bodyRows = '';
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $item = trim((string) ($row['item'] ?? ''));
            $quantity = trim((string) ($row['quantity'] ?? ''));
            $notes = trim((string) ($row['notes'] ?? ''));
            if ($item === '' && $quantity === '' && $notes === '') {
                continue;
            }
            $bodyRows .= '<tr>';
            $bodyRows .= '<td>' . esc($item) . '</td>';
            $bodyRows .= '<td>' . esc($quantity) . '</td>';
            $bodyRows .= '<td>' . esc($notes) . '</td>';
            $bodyRows .= '</tr>';
        }

        if ($bodyRows === '') {
            return '';
        }

        $contact = trim((string) ($b['contact'] ?? ''));
        $footnote = trim((string) ($b['footnote'] ?? ''));

        $html = '<div class="content-section project-material-needs" id="project-material-needs">';
        $html .= '<div class="content-title">' . esc($sectionTitle) . '</div>';
        $html .= '<table class="material-needs-table"><thead><tr>';
        $html .= '<th>' . esc((string) lang('Projects.material_needs_col_item')) . '</th>';
        $html .= '<th>' . esc((string) lang('Projects.material_needs_col_quantity')) . '</th>';
        $html .= '<th>' . esc((string) lang('Projects.material_needs_col_notes')) . '</th>';
        $html .= '</tr></thead><tbody>' . $bodyRows . '</tbody></table>';
        if ($contact !== '') {
            $html .= '<p class="material-needs-contact small mb-0 mt-2"><strong>' . esc((string) lang('Projects.material_needs_contact')) . '</strong> ' . esc($contact) . '</p>';
        }
        if ($footnote !== '') {
            $html .= '<p class="small text-muted mt-2 mb-0">' . nl2br(esc($footnote), false) . '</p>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function timeline(array $b): string
    {
        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = '📅 Calendrier';
        }
        $phases = $b['phases'] ?? [];
        if (! is_array($phases) || $phases === []) {
            return '';
        }
        $phHtml = '';
        foreach ($phases as $ph) {
            if (! is_array($ph)) {
                continue;
            }
            $label = trim((string) ($ph['phase_label'] ?? ''));
            $dur = trim((string) ($ph['duration'] ?? ''));
            $title = trim((string) ($ph['step_title'] ?? ''));
            $body = trim((string) ($ph['body'] ?? ''));
            if ($label === '' && $dur === '' && $title === '' && $body === '') {
                continue;
            }
            $phHtml .= '<div class="phase"><div class="phase-num">' . esc($label);
            if ($dur !== '') {
                $phHtml .= '<span class="phase-duration">' . esc($dur) . '</span>';
            }
            $phHtml .= '</div><div>';
            if ($title !== '') {
                $phHtml .= '<h5>' . esc($title) . '</h5>';
            }
            if ($body !== '') {
                $phHtml .= '<p>' . nl2br(esc($body), false) . '</p>';
            }
            $phHtml .= '</div></div>';
        }
        if ($phHtml === '') {
            return '';
        }

        return '<div class="content-section"><div class="content-title">' . esc($sectionTitle)
            . '</div><div class="phases">' . $phHtml . '</div></div>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function kpiGrid(array $b): string
    {
        helper('admin');
        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = "📊 Indicateurs d'impact";
        }
        $style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
        $titleClass = 'content-title' . ($style === 'warm' ? ' warm' : ($style === 'default' ? '' : ' teal'));

        $items = $b['items'] ?? [];
        if (! is_array($items) || $items === []) {
            return '';
        }
        $cards = '';
        foreach ($items as $it) {
            if (! is_array($it)) {
                continue;
            }
            $v = admin_pp_scrub_junk_text(trim((string) ($it['value'] ?? '')));
            $l = admin_pp_scrub_junk_text(trim((string) ($it['label'] ?? '')));
            if ($v === '' && $l === '') {
                continue;
            }
            $cards .= '<div class="kpi-card"><div class="kpi-n">' . esc($v) . '</div><div class="kpi-l">' . esc($l) . '</div></div>';
        }
        if ($cards === '') {
            return '';
        }

        return '<div class="content-section"><div class="' . esc($titleClass, 'attr') . '">' . esc($sectionTitle)
            . '</div><div class="kpis-grid">' . $cards . '</div></div>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function notePanel(array $b): string
    {
        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = "🎯 Suivi d'impact";
        }
        $style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
        $titleClass = 'content-title' . ($style === 'warm' ? ' warm' : ($style === 'default' ? '' : ' teal'));
        $message = trim((string) ($b['message'] ?? ''));
        if ($message === '') {
            return '';
        }
        $sub = trim((string) ($b['submessage'] ?? ''));
        $box = '<div style="text-align:center;padding:1.75rem 1.25rem;color:var(--text-muted);font-size:.85rem;border:1px dashed var(--border-dim);border-radius:6px;line-height:2">'
            . nl2br(esc($message), false);
        if ($sub !== '') {
            $box .= '<div style="font-size:.73rem;margin-top:.5rem;opacity:.7">' . nl2br(esc($sub), false) . '</div>';
        }
        $box .= '</div>';

        return '<div class="content-section"><div class="' . esc($titleClass, 'attr') . '">' . esc($sectionTitle) . '</div>' . $box . '</div>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function impactTracker(array $b): string
    {
        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = "🎯 Suivi d'impact — Résultats actuels";
        }
        $style = strtolower(trim((string) ($b['heading_style'] ?? 'teal')));
        $titleClass = 'content-title' . ($style === 'warm' ? ' warm' : ($style === 'default' ? '' : ' teal'));

        $note = trim((string) ($b['note'] ?? ''));
        $rows = $b['rows'] ?? [];
        if (! is_array($rows) || $rows === []) {
            return '';
        }

        $rowsHtml = '';
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $numbers = trim((string) ($row['numbers'] ?? ''));
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

            $rowsHtml .= '<div>';
            $rowsHtml .= '<div class="impact-row-header">';
            $rowsHtml .= '<span class="impact-label">' . esc($label) . '</span>';
            $rowsHtml .= '<span class="impact-numbers">' . esc($numbers) . '</span>';
            $rowsHtml .= '</div>';
            $rowsHtml .= '<div class="impact-bar-wrap"><div class="impact-bar-fill" style="width:' . $pct . '%"></div></div>';
            $rowsHtml .= '</div>';
        }

        if ($rowsHtml === '') {
            return '';
        }

        $html = '<div class="content-section">';
        $html .= '<div class="' . esc($titleClass, 'attr') . '">' . esc($sectionTitle) . '</div>';
        if ($note !== '') {
            $html .= '<p class="impact-note">' . esc($note) . '</p>';
        }
        $html .= '<div class="impact-tracker">' . $rowsHtml . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function team(array $b): string
    {
        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = '👥 Équipe projet';
        }
        $members = $b['members'] ?? [];
        if (! is_array($members) || $members === []) {
            return '';
        }
        $rows = '';
        foreach ($members as $m) {
            if (! is_array($m)) {
                continue;
            }
            $name = trim((string) ($m['name'] ?? ''));
            $role = trim((string) ($m['role'] ?? ''));
            if ($name === '' && $role === '') {
                continue;
            }
            $rows .= '<div class="team-row"><div class="team-avatar">👤</div><div class="team-info"><strong>' . esc($name)
                . '</strong><br><small>' . esc($role) . '</small></div></div>';
        }
        if ($rows === '') {
            return '';
        }

        return '<div class="content-section"><div class="content-title">' . esc($sectionTitle)
            . '</div><div class="team-list">' . $rows . '</div></div>';
    }

    /**
     * @param array<string, mixed> $b
     */
    private static function sources(array $b): string
    {
        $sectionTitle = trim((string) ($b['section_title'] ?? ''));
        if ($sectionTitle === '') {
            $sectionTitle = '📎 Sources & documents';
        }
        $lines = $b['lines'] ?? [];
        if (! is_array($lines) || $lines === []) {
            return '';
        }
        $lis = '';
        foreach ($lines as $line) {
            if (! is_string($line)) {
                continue;
            }
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $lis .= '<li>' . esc($line) . '</li>';
        }
        if ($lis === '') {
            return '';
        }

        return '<div class="content-section"><div class="content-title">' . esc($sectionTitle) . '</div><ul>' . $lis . '</ul></div>';
    }
}
