<?php

declare(strict_types=1);

use App\Libraries\ProjectGeographyPayload;
use App\Models\MdgRegionModel;

/** @var array<string, mixed>|null $project */
$geoState = ProjectGeographyPayload::formStateForView($project);
$geoInitialJson = json_encode([
    'national'      => $geoState['national'],
    'region_ids'    => $geoState['region_ids'],
    'district_ids'  => $geoState['district_ids'],
    'commune_ids'   => $geoState['commune_ids'],
    'fokontany_ids' => $geoState['fokontany_ids'],
], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
$geoNational = $geoState['national'];
$geoLegacy = $geoState['legacy'];

$mdgRegions = [];
$geoCatalogReady = false;
try {
    if (db_connect()->tableExists('mdg_regions')) {
        $geoCatalogReady = (int) db_connect()->table('mdg_regions')->countAllResults() > 0;
        if ($geoCatalogReady) {
            $mdgRegions = model(MdgRegionModel::class)->listForSelect();
        }
    }
} catch (\Throwable) {
    $mdgRegions = [];
}
$selectedRegions = array_flip($geoState['region_ids']);
$geoLocale = old('locale', is_array($project) ? (string) ($project['locale'] ?? 'fr') : 'fr');
$geoLocale = $geoLocale === 'en' ? 'en' : 'fr';
$geoPreview = ProjectGeographyPayload::previewFromFormState($geoState, $geoLocale);
$geoPreviewHtml = $geoPreview['html'] !== '' ? $geoPreview['html'] : '—';
$geoLabelsJson = json_encode(
    ProjectGeographyPayload::countLabelTemplates($geoLocale),
    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
);
?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/project-geo-tooltip.css'), 'attr') ?>">
<div class="col-12" id="pp-geo-picker"
     data-api-base="<?= esc(base_url('admin/geo'), 'attr') ?>"
     data-catalog-ready="<?= $geoCatalogReady ? '1' : '0' ?>">
    <label class="form-label"><?= esc(lang('Admin.form_geo_label')) ?></label>

    <?php if (! $geoCatalogReady) : ?>
        <p class="alert alert-warning small py-2 mb-2"><?= lang('Admin.help_geo_catalog_empty') ?></p>
    <?php endif; ?>

    <?php if ($geoLegacy !== '') : ?>
        <p class="small text-warning mb-2"><?= esc(lang('Admin.help_geo_legacy', [$geoLegacy])) ?></p>
    <?php endif; ?>

    <div class="form-check mb-2">
        <input type="checkbox" class="form-check-input" name="geo_national" id="pp-geo-national" value="1"
            <?= $geoNational ? 'checked' : '' ?>>
        <label class="form-check-label" for="pp-geo-national"><?= esc(lang('Admin.form_geo_national')) ?></label>
    </div>

    <div id="pp-geo-cascade" class="<?= $geoNational ? 'd-none' : '' ?>">
        <div class="row g-2">
            <div class="col-md-6 col-lg-3">
                <label for="pp-geo-regions" class="form-label small mb-1"><?= esc(lang('Admin.form_geo_regions')) ?></label>
                <select name="geo_region_ids[]" id="pp-geo-regions" class="form-select" multiple size="7"
                        aria-describedby="pp-geo-regions-hint" <?= $geoCatalogReady ? '' : 'disabled' ?>>
                    <?php foreach ($mdgRegions as $row) :
                        $rid = (int) ($row['id'] ?? 0);
                        if ($rid <= 0) {
                            continue;
                        }
                        $sel = isset($selectedRegions[$rid]) ? ' selected' : '';
                        ?>
                    <option value="<?= $rid ?>"<?= $sel ?>><?= esc((string) ($row['name'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
                <p id="pp-geo-regions-hint" class="form-text mb-0"><?= esc(lang('Admin.help_geo_multi_select')) ?></p>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="pp-geo-districts" class="form-label small mb-1"><?= esc(lang('Admin.form_geo_districts')) ?></label>
                <select name="geo_district_ids[]" id="pp-geo-districts" class="form-select" multiple size="7" disabled></select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="pp-geo-communes" class="form-label small mb-1"><?= esc(lang('Admin.form_geo_communes')) ?></label>
                <select name="geo_commune_ids[]" id="pp-geo-communes" class="form-select" multiple size="7" disabled></select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="pp-geo-fokontany" class="form-label small mb-1"><?= esc(lang('Admin.form_geo_fokontany')) ?></label>
                <select name="geo_fokontany_ids[]" id="pp-geo-fokontany" class="form-select" multiple size="7" disabled></select>
            </div>
        </div>
    </div>

    <p class="form-text mt-2 mb-0">
        <?= esc(lang('Admin.help_geo_preview')) ?>
        <strong id="pp-geo-preview"><?= $geoPreviewHtml ?></strong>
    </p>
    <p id="pp-geo-status" class="small text-muted mb-0" role="status"></p>

    <script type="application/json" id="pp-geo-initial"><?= $geoInitialJson ?></script>
    <script type="application/json" id="pp-geo-labels"><?= $geoLabelsJson ?></script>
</div>
