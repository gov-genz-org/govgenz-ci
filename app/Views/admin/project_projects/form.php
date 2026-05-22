<?php

declare(strict_types=1);

helper(['form', 'admin', 'project']);

use App\Models\ProjectProjectModel;

/** @var array<string, mixed>|null $project */
/** @var list<array<string, mixed>> $sectors */
/** @var list<array<string, mixed>> $blocksForForm */
/** @var string $bodyContentMode */
/** @var bool $canUseAdvancedHtml */
/** @var bool $bodyLockedLegacyHtml */
/** @var string $bodyStoredHtml */
/** @var bool $bodyOrphanHtml */
/** @var string|null $publicPreviewUrl */
$blocksForForm = $blocksForForm ?? [];
$bodyContentMode = $bodyContentMode ?? 'blocks';
$canUseAdvancedHtml = $canUseAdvancedHtml ?? false;
$bodyLockedLegacyHtml = $bodyLockedLegacyHtml ?? false;
$bodyStoredHtml = $bodyStoredHtml ?? '';
$bodyOrphanHtml = $bodyOrphanHtml ?? false;
$publicPreviewUrl = $publicPreviewUrl ?? null;
$isEdit = $project !== null;
$action = $isEdit
    ? site_url('admin/project-projects/update/' . (int) ($project['id'] ?? 0))
    : site_url('admin/project-projects/store');
$oldSectors = old('sectors');
if (is_array($oldSectors)) {
    $selectedSectors = [];
    foreach ($oldSectors as $v) {
        if (! is_string($v)) {
            continue;
        }
        $c = strtolower(trim($v));
        if ($c !== '') {
            $selectedSectors[] = $c;
        }
    }
    $selectedSectors = array_values(array_unique($selectedSectors));
} elseif ($isEdit) {
    $selectedSectors = array_values(array_unique(array_filter(array_map(static function ($c): string {
        return strtolower(trim((string) $c));
    }, explode(',', (string) ($project['sectors_csv'] ?? ''))))));
} else {
    $selectedSectors = [];
}
$ppContentMode = old('body_content_mode', $bodyContentMode ?? 'blocks');
if (! in_array($ppContentMode, ['html', 'blocks'], true)) {
    $ppContentMode = 'blocks';
}
$ppLocale = old('locale', $isEdit ? (string) ($project['locale'] ?? 'fr') : 'fr');
if (! in_array($ppLocale, ['fr', 'en'], true)) {
    $ppLocale = 'fr';
}
$ppTg = old('translation_group', $isEdit ? (string) ($project['translation_group'] ?? '') : '');

$budgetAmount = (string) old('budget_amount', '');
$budgetScale = (string) old('budget_scale', ProjectProjectModel::BUDGET_SCALE_MILLION);
if ($budgetAmount === '' && $isEdit) {
    if (isset($project['budget_amount']) && $project['budget_amount'] !== null && $project['budget_amount'] !== '') {
        $budgetAmount = rtrim(rtrim(sprintf('%.4F', (float) $project['budget_amount']), '0'), '.');
        $budgetScale = (string) ($project['budget_scale'] ?? ProjectProjectModel::BUDGET_SCALE_MILLION);
    } elseif (trim((string) ($project['budget_display'] ?? '')) !== '') {
        $legacyParts = project_budget_infer_parts_from_legacy((string) $project['budget_display']);
        if ($legacyParts !== null) {
            $budgetAmount = rtrim(rtrim(sprintf('%.4F', $legacyParts['amount']), '0'), '.');
            $budgetScale = $legacyParts['scale'];
        }
    }
}
if (! in_array($budgetScale, ProjectProjectModel::budgetScaleCodes(), true)) {
    $budgetScale = ProjectProjectModel::BUDGET_SCALE_MILLION;
}
$budgetPreview = '';
if ($budgetAmount !== '' && is_numeric($budgetAmount) && (float) $budgetAmount > 0) {
    $budgetPreview = project_format_budget_display_from_parts((float) $budgetAmount, $budgetScale, $ppLocale);
} elseif ($isEdit) {
    $budgetPreview = trim((string) ($project['budget_display'] ?? ''));
}
$budgetErrors = session('errors') ?? [];
$budgetSuffixesJson = json_encode([
    'fr' => [
        'ariary'   => 'Ar',
        'thousand' => 'k Ar',
        'million'  => 'M Ar',
        'billion'  => 'Mds Ar',
    ],
    'en' => [
        'ariary'   => 'Ar',
        'thousand' => 'k Ar',
        'million'  => 'M Ar',
        'billion'  => 'bn Ar',
    ],
], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<h1 class="h3 mb-1"><?= esc($isEdit ? lang('Admin.form_project_edit') : lang('Admin.form_project_new')) ?></h1>
<p class="text-muted small mb-3">
    Remplissez la <strong>carte</strong> (liste + en-tête), puis les <strong>sections</strong> du détail avec les boutons « + Section », « + Budget », etc.
    Aucune compétence technique requise.
</p>

<?php if ($publicPreviewUrl !== null) : ?>
    <p class="mb-3">
        <a href="<?= esc($publicPreviewUrl, 'attr') ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Voir la fiche publiée</a>
    </p>
<?php endif; ?>

<form method="post" action="<?= esc($action) ?>" class="admin-editor-form border rounded bg-white shadow-sm p-3 p-md-4">
    <?= csrf_field() ?>

    <?php if (! $canUseAdvancedHtml) : ?>
        <input type="hidden" name="body_content_mode" value="<?= esc($ppContentMode, 'attr') ?>">
    <?php endif; ?>

    <h2 class="h6 text-uppercase text-muted border-bottom pb-2 mb-3">1. Carte &amp; en-tête</h2>
    <p class="small text-muted mb-3">Visible sur la liste et en haut de la fiche (titre, chapô, secteurs, chiffres clés).</p>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="pp-slug" class="form-label">Identifiant URL (slug) <span class="text-danger">*</span></label>
            <input type="text" name="slug" id="pp-slug" class="form-control" required maxlength="160"
                   value="<?= esc(old('slug', $isEdit ? (string) ($project['slug'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-6">
            <label for="pp-title" class="form-label">Titre <span class="text-danger">*</span></label>
            <input type="text" name="title" id="pp-title" class="form-control" required maxlength="255"
                   value="<?= esc(old('title', $isEdit ? (string) ($project['title'] ?? '') : '')) ?>">
        </div>
        <div class="col-md-6">
            <label for="pp-locale" class="form-label">Langue <span class="text-danger">*</span></label>
            <?php if ($isEdit) : ?>
                <input type="text" id="pp-locale" class="form-control bg-light" readonly
                       value="<?= esc($ppLocale === 'en' ? 'English (en)' : 'Français (fr)') ?>">
            <?php else : ?>
                <select name="locale" id="pp-locale" class="form-select" required>
                    <option value="fr" <?= $ppLocale === 'fr' ? 'selected' : '' ?>>Français (fr)</option>
                    <option value="en" <?= $ppLocale === 'en' ? 'selected' : '' ?>>English (en)</option>
                </select>
            <?php endif; ?>
            <div class="form-text"><?= esc(lang('Admin.help_project_locale')) ?></div>
        </div>
        <?php if ($canUseAdvancedHtml) : ?>
        <div class="col-md-6">
            <label for="pp-tg" class="form-label">Groupe de traduction</label>
            <input type="text" name="translation_group" id="pp-tg" class="form-control font-monospace" maxlength="64"
                   placeholder="ex. id du projet FR ou libellé partagé"
                   value="<?= esc($ppTg) ?>">
            <div class="form-text">Optionnel à la création (défaut = id). Même valeur sur la fiche FR et EN pour les relier (comme le CMS).</div>
        </div>
        <?php endif; ?>
        <div class="col-12">
            <label for="pp-excerpt" class="form-label">Accroche (carte liste)</label>
            <textarea name="excerpt" id="pp-excerpt" class="form-control" rows="3"><?= esc(old('excerpt', $isEdit ? (string) ($project['excerpt'] ?? '') : '')) ?></textarea>
        </div>
        
        <div class="col-md-4">
            <label for="pp-pstatus" class="form-label">Statut métier</label>
            <select name="project_status" id="pp-pstatus" class="form-select">
                <?php foreach (\App\Models\ProjectProjectModel::projectStatusLabels() as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= old('project_status', $isEdit ? (string) ($project['project_status'] ?? '') : \App\Models\ProjectProjectModel::STATUS_CANDIDAT) === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="pp-pub" class="form-label">Publication</label>
            <select name="publication_state" id="pp-pub" class="form-select">
                <?php foreach (\App\Models\ProjectProjectModel::publicationStateLabels() as $k => $lab) : ?>
                    <option value="<?= esc($k) ?>" <?= old('publication_state', $isEdit ? (string) ($project['publication_state'] ?? '') : \App\Models\ProjectProjectModel::PUBLICATION_DRAFT) === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="pp-vol" class="form-label">Volontaires (nombre)</label>
            <input type="number" name="volunteers_count" id="pp-vol" class="form-control" min="0" step="1"
                   value="<?= esc((string) old('volunteers_count', $isEdit ? (string) (int) ($project['volunteers_count'] ?? 0) : '0')) ?>">
        </div>
        <div class="col-12">
            <label for="pp-sectors" class="form-label">Secteurs</label>
            <select name="sectors[]" id="pp-sectors" class="form-select" multiple size="8" aria-describedby="pp-sectors-hint">
                <?php foreach ($sectors as $s) :
                    $code = strtolower(trim((string) ($s['code'] ?? '')));
                    if ($code === '') {
                        continue;
                    }
                    $pill = trim((string) ($s['code_fr'] ?? ''));
                    $optShort = $pill !== '' ? $pill : (strtoupper($code[0]) . substr($code, 1));
                    $sel = in_array($code, $selectedSectors, true) ? ' selected' : '';
                    ?>
                <option value="<?= esc($code) ?>"<?= $sel ?>><?= esc((string) ($s['label_fr'] ?? $code)) ?> — <?= esc($optShort) ?> (<?= esc($code) ?>)</option>
                <?php endforeach; ?>
            </select>
            <p id="pp-sectors-hint" class="form-text mb-0">Sélection multiple : Ctrl-clic (Windows) ou Cmd-clic (macOS).</p>
        </div>
        <div class="col-md-3">
            <label for="pp-budget-amount" class="form-label">Budget — montant</label>
            <input type="number" name="budget_amount" id="pp-budget-amount"
                   class="form-control <?= isset($budgetErrors['budget_amount']) ? 'is-invalid' : '' ?>"
                   min="0" step="any" placeholder="ex. 195"
                   value="<?= esc($budgetAmount) ?>">
            <?php if (isset($budgetErrors['budget_amount'])) : ?>
                <div class="invalid-feedback"><?= esc($budgetErrors['budget_amount']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-3">
            <label for="pp-budget-scale" class="form-label">Unité</label>
            <select name="budget_scale" id="pp-budget-scale"
                    class="form-select <?= isset($budgetErrors['budget_scale']) ? 'is-invalid' : '' ?>">
                <?php foreach (ProjectProjectModel::budgetScaleLabels() as $code => $lab) : ?>
                    <option value="<?= esc($code) ?>" <?= $budgetScale === $code ? 'selected' : '' ?>><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($budgetErrors['budget_scale'])) : ?>
                <div class="invalid-feedback"><?= esc($budgetErrors['budget_scale']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Aperçu affiché (liste + fiche)</label>
            <p id="pp-budget-preview" class="form-control bg-light mb-0" style="min-height:2.4rem"><?= esc($budgetPreview !== '' ? $budgetPreview : '—') ?></p>
            <p class="form-text mb-0">Avec un bloc « Tableau budget », le montant et l’aperçu sont calculés automatiquement à partir de la somme des lignes (hors ligne Total).</p>
            <div id="pp-budget-live" class="d-none" aria-hidden="true"
                 data-locale="<?= esc($ppLocale, 'attr') ?>"
                 data-suffixes="<?= esc($budgetSuffixesJson, 'attr') ?>"></div>
        </div>
        <?= view('admin/project_projects/partials/geography_fields', ['project' => $project]) ?>
        <div class="col-md-4">
            <label for="pp-launch" class="form-label">Lancement</label>
            <input type="date" name="launched_at" id="pp-launch" class="form-control"
                   value="<?= esc(old('launched_at', $isEdit && ! empty($project['launched_at']) ? (string) $project['launched_at'] : '')) ?>">
        </div>
        <div class="col-md-4">
            <label for="pp-dur" class="form-label">Durée (mois)</label>
            <input type="number" name="duration_months" id="pp-dur" class="form-control" min="0" step="1"
                   value="<?= esc(old('duration_months', $isEdit && isset($project['duration_months']) && $project['duration_months'] !== null ? (string) (int) $project['duration_months'] : '')) ?>">
        </div>
        <div class="col-md-4">
            <label for="pp-prog" class="form-label">Avancement (%)</label>
            <input type="number" name="progress_percent" id="pp-prog" class="form-control" min="0" max="100" step="1"
                   value="<?= esc(old('progress_percent', $isEdit && isset($project['progress_percent']) && $project['progress_percent'] !== null ? (string) (int) $project['progress_percent'] : '')) ?>">
        </div>
        <?php if ($canUseAdvancedHtml) : ?>
        <div class="col-md-4">
            <label for="pp-mt" class="form-label">Titre SEO (Google)</label>
            <input type="text" name="meta_title" id="pp-mt" class="form-control" maxlength="255"
                   value="<?= esc(old('meta_title', $isEdit ? (string) ($project['meta_title'] ?? '') : '')) ?>">
        </div>
        <div class="col-12">
            <label for="pp-md" class="form-label">Description SEO</label>
            <textarea name="meta_description" id="pp-md" class="form-control" rows="2" maxlength="512"><?= esc(old('meta_description', $isEdit ? (string) ($project['meta_description'] ?? '') : '')) ?></textarea>
        </div>
        <?php endif; ?>

        <div class="col-12 mt-2">
            <h2 class="h6 text-uppercase text-muted border-bottom pb-2 mb-3">2. Contenu du détail (sections)</h2>
            <?php if ($bodyLockedLegacyHtml) : ?>
                <div class="alert alert-warning small mb-0">
                    Cette fiche utilise encore l’ancien mode HTML. Un administrateur doit la convertir en blocs avant que vous puissiez modifier le texte long.
                    Vous pouvez toutefois mettre à jour la carte et les chiffres ci-dessus.
                </div>
            <?php else : ?>
                <p class="text-muted small mb-2">
                    Ajoutez les sections une par une : problème, solution, budget, calendrier, indicateurs, équipe, sources.
                    Le site applique automatiquement la mise en page.
                </p>
                <?php if ($canUseAdvancedHtml) : ?>
                    <?php if ($bodyOrphanHtml) : ?>
                    <div class="alert alert-info small mb-2">
                        Cette fiche est en mode <strong>Blocs</strong> (c’est ce qui s’affiche sur le site), mais une ancienne copie HTML est encore en base dans <code>body</code>.
                        Cliquez sur <strong>HTML avancé (admin)</strong> pour l’ouvrir dans TinyMCE.
                        Enregistrer en mode Blocs ne modifie pas ce HTML tant que vous ne repassez pas en mode HTML.
                    </div>
                    <?php endif; ?>
                    <div class="btn-group flex-wrap mb-2" role="group" aria-label="Mode corps">
                        <input type="radio" class="btn-check" name="body_content_mode" id="pp-cm-html" value="html" autocomplete="off" <?= $ppContentMode === 'html' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-secondary btn-sm" for="pp-cm-html">HTML avancé (admin)</label>
                        <input type="radio" class="btn-check" name="body_content_mode" id="pp-cm-blocks" value="blocks" autocomplete="off" <?= $ppContentMode === 'blocks' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-secondary btn-sm" for="pp-cm-blocks">Blocs (recommandé)</label>
                    </div>
                    <div id="pp-html-panel" class="<?= $ppContentMode === 'blocks' ? 'd-none' : '' ?>">
                        <label for="pp-body" class="form-label small">HTML (réservé admin)</label>
                        <textarea name="body" id="pp-body" class="form-control" rows="12"><?= old('body', $isEdit ? (string) ($project['body'] ?? '') : '') ?></textarea>
                    </div>
                <?php endif; ?>
                <?= view('admin/project_projects/blocks/builder', [
                    'contentMode'        => $ppContentMode,
                    'blocksForForm'      => $blocksForForm,
                    'canUseAdvancedHtml' => $canUseAdvancedHtml,
                    'ppLocale'           => $ppLocale,
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= site_url('admin/project-projects') ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
