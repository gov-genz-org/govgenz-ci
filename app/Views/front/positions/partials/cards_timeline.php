<?php

declare(strict_types=1);

helper(['position', 'language']);

use App\Models\PositionItemModel;

/** @var list<array<string, mixed>> $positions */
/** @var array<string, string> $sectorOptions */
/** @var array<string, string> $sectorFilterPills */
/** @var array<string, string> $typeLabels */

$loc = \App\Libraries\SiteContext::locale();

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

$typeBadgeClass = static function (string $code): string {
    return match ($code) {
        PositionItemModel::TYPE_DENIAL   => 'badge-denial',
        PositionItemModel::TYPE_PRAISE   => 'badge-praise',
        PositionItemModel::TYPE_ANALYSIS => 'badge-analysis',
        PositionItemModel::TYPE_SOLUTION => 'badge-solution',
        default                          => '',
    };
};
?>
<?php if ($positions === []) : ?>
    <div class="positions-program-page__no-results js-positions-no-results" role="status">
        <?= esc(lang('Positions.empty_state')) ?>
    </div>
<?php else : ?>
    <div class="positions-program-page__no-results js-positions-no-results" role="status" hidden>
        <?= esc(lang('Positions.empty_state')) ?>
    </div>
    <div class="positions-program-page__timeline" id="positions-list">
        <?php foreach ($positions as $p) :
            $slug = (string) ($p['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $href      = position_public_url($slug);
            $title     = (string) ($p['title'] ?? $slug);
            $summary   = trim((string) ($p['summary'] ?? ''));
            $typesCsv  = (string) ($p['types_csv'] ?? '');
            $types     = position_types_from_csv($typesCsv);
            $csv       = trim((string) ($p['sectors_csv'] ?? ''));
            $secRaw    = $csv === '' ? [] : array_map('trim', explode(',', $csv));
            $secCodes  = [];
            foreach ($secRaw as $x) {
                $lc = strtolower($x);
                if ($lc !== '') {
                    $secCodes[$lc] = true;
                }
            }
            $dataSectors = implode(',', array_keys($secCodes));
            $dataTypes   = implode(',', $types);
            $dateIso     = substr((string) ($p['published_at'] ?? ''), 0, 10);
            $dateLabel   = position_format_published_date((string) ($p['published_at'] ?? ''), $loc);
            $readLabel   = position_reading_label($p, $loc);
            $bandClass   = position_type_band_class($typesCsv);
            $bandLabel   = position_type_band_label($typesCsv);
            ?>
            <a
                class="positions-program-page__avis-card positions-program-page__card js-positions-avis-card"
                href="<?= esc($href, 'attr') ?>"
                <?= $dataSectors !== '' ? 'data-sectors="' . esc($dataSectors, 'attr') . '"' : '' ?>
                <?= $dataTypes !== '' ? 'data-types="' . esc($dataTypes, 'attr') . '"' : '' ?>
                <?= $dateIso !== '' ? 'data-date="' . esc($dateIso, 'attr') . '"' : '' ?>
            >
                <?php if ($bandLabel !== '') : ?>
                    <div class="positions-program-page__card-band positions-program-page__card-band--<?= esc($bandClass, 'attr') ?>">
                        <?= esc($bandLabel) ?>
                    </div>
                <?php endif; ?>

                <div class="positions-program-page__avis-header">
                    <div class="positions-program-page__avis-meta">
                        <?php if ($dateLabel !== '') : ?>
                            <span>📅 <?= esc($dateLabel) ?></span>
                        <?php endif; ?>
                        <?php if ($readLabel !== '') : ?>
                            <span>⏱ <?= esc($readLabel) ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="positions-program-page__avis-title"><?= esc($title) ?></h3>
                    <div class="positions-program-page__badges">
                        <?php foreach (array_slice(array_keys($secCodes), 0, 4) as $sc) : ?>
                            <span class="positions-program-page__badge positions-program-page__badge-sector"><?= esc($sectorTagLabel($sectorFilterPills, $sectorOptions, $sc)) ?></span>
                        <?php endforeach; ?>
                        <?php foreach ($types as $tc) :
                            $tLab = $typeLabels[$tc] ?? $tc;
                            ?>
                            <span class="positions-program-page__badge positions-program-page__badge-<?= esc($typeBadgeClass($tc), 'attr') ?>"><?= esc($tLab) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($summary !== '') : ?>
                    <div class="positions-program-page__avis-summary"><?= nl2br(esc($summary)) ?></div>
                <?php endif; ?>

                <div class="positions-program-page__avis-footer">
                    <span class="positions-program-page__card-link"><?= esc(lang('Positions.card_view_link')) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
