<?php

declare(strict_types=1);

helper(['project', 'language']);

use App\Models\ProjectProjectModel;

/** @var list<array<string, mixed>> $projects */
/** @var array<string, string> $sectorOptions */
/** @var array<string, string> $sectorFilterPills codes (minuscules) => libellé court filtres (même clé que POST filtre secteur) */
/** @var array<string, string> $statusLabels */

$sectorFilterPills = $sectorFilterPills ?? [];

$sectorTagLabel = static function (array $sectorFilterPills, array $sectorOptions, string $code): string {
    $c = strtolower(trim($code));
    if ($c === '') {
        return '';
    }
    if (isset($sectorFilterPills[$c])) {
        return (string) $sectorFilterPills[$c];
    }
    foreach ($sectorOptions as $k => $label) {
        if (strcasecmp((string) $k, $c) === 0) {
            return (string) $label;
        }
    }

    return $c;
};

$statusBadgeClass = static function (string $s): string {
    return match ($s) {
        ProjectProjectModel::STATUS_ACTIF       => 'status-actif',
        ProjectProjectModel::STATUS_CANDIDAT    => 'status-candidat',
        ProjectProjectModel::STATUS_VALIDATION => 'status-validation',
        ProjectProjectModel::STATUS_COMPLETE    => 'status-complete',
        default                                 => '',
    };
};
?>
<?php if ($projects === []) : ?>
    <div class="projects-program-page__empty">
        <?= esc(lang('Projects.empty_state')) ?>
    </div>
<?php else : ?>
    <div class="projects-program-page__grid">
        <?php foreach ($projects as $p) :
            $slug = (string) ($p['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $href   = project_public_url($slug);
            $title  = (string) ($p['title'] ?? $slug);
            $ex     = trim((string) ($p['excerpt'] ?? ''));
            $exShow = $ex !== '' ? (mb_strlen($ex) > 180 ? mb_substr($ex, 0, 180) . '…' : $ex) : '';
            $st     = (string) ($p['project_status'] ?? '');
            $stLab  = $statusLabels[$st] ?? $st;
            $badge  = $statusBadgeClass($st);
            $csv    = trim((string) ($p['sectors_csv'] ?? ''));
            $secRaw = $csv === '' ? [] : array_map('trim', explode(',', $csv));
            $secCodesNorm = [];
            foreach ($secRaw as $x) {
                $lc = strtolower($x);
                if ($lc !== '' && ! isset($secCodesNorm[$lc])) {
                    $secCodesNorm[$lc] = true;
                }
            }
            $secCodesList = array_slice(array_keys($secCodesNorm), 0, 4);
            $dataSectors  = implode(',', array_keys($secCodesNorm));
            $geoDisplay = project_geography_front_display($p);
            $budget = trim((string) ($p['budget_display'] ?? ''));
            $vol    = (int) ($p['volunteers_count'] ?? 0);
            ?>
            <a class="projects-program-page__card" href="<?= esc($href, 'attr') ?>"
                <?= $dataSectors !== '' ? 'data-sectors="' . esc($dataSectors, 'attr') . '"' : '' ?>
                <?= $st !== '' ? 'data-status="' . esc($st, 'attr') . '"' : '' ?>>
                <div class="projects-program-page__tags">
                    <?php foreach ($secCodesList as $sc) : ?>
                        <span class="projects-program-page__tag"><?= esc($sectorTagLabel($sectorFilterPills, $sectorOptions, $sc)) ?></span>
                    <?php endforeach; ?>
                    <?php if ($st !== '') : ?>
                        <span class="projects-program-page__status<?= $badge !== '' ? ' ' . esc($badge, 'attr') : '' ?>"><?= esc($stLab) ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="projects-program-page__card-title"><?= esc($title) ?></h3>
                <p class="projects-program-page__card-excerpt"><?= $exShow !== '' ? esc($exShow) : '&#8203;' ?></p>
                <div class="projects-program-page__card-foot">
                    <div class="projects-program-page__meta-row">
                        <?php if ($vol > 0) : ?>
                            <span>👥 <strong><?= esc((string) $vol) ?></strong> <?= esc(lang('Projects.card_volunteers')) ?></span>
                        <?php endif; ?>
                        <?php if ($budget !== '') : ?>
                            <span>💰 <strong><?= esc($budget) ?></strong></span>
                        <?php endif; ?>
                        <?php if ($geoDisplay['html'] !== '') : ?>
                            <span class="projects-program-page__meta-geo">📍 <?= $geoDisplay['html'] ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="projects-program-page__card-link"><?= esc(lang('Projects.card_view_link')) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
