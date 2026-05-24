<?php

declare(strict_types=1);

helper(['project', 'language']);

/** @var array<string, mixed> $project */

$showFundBudget   = project_has_financial_funding($project);
$showFundMaterial = project_has_material_needs($project);
if (! $showFundBudget && ! $showFundMaterial) {
    return;
}

$slug = trim((string) ($project['slug'] ?? ''));
if ($slug === '') {
    return;
}

$fundSuccess = session()->getFlashdata('fund_success');
$fundErrors  = session()->getFlashdata('fund_errors');
if (! is_array($fundErrors)) {
    $fundErrors = $fundErrors !== null && $fundErrors !== '' ? [(string) $fundErrors] : [];
}
$fundErrors = array_values(array_filter(array_map('strval', $fundErrors)));
$fundSuccess = is_string($fundSuccess) && $fundSuccess !== '' ? $fundSuccess : null;

$autoOpen = $fundSuccess !== null || $fundErrors !== [];
?>
<dialog
    id="project-fund"
    class="project-fund-dialog project-fund-drawer"
    data-fund-modal
    <?= $autoOpen ? ' data-fund-modal-autopen' : '' ?>
>
    <div class="project-fund-dialog__panel" role="document">
        <button type="button" class="project-fund-dialog__close" data-fund-modal-close aria-label="<?= esc(lang('Projects.fund_modal_close'), 'attr') ?>">×</button>

        <header class="project-fund-dialog__header">
            <h2 id="project-fund-heading" class="project-fund-dialog__title"><?= esc(lang('Projects.fund_form_title')) ?></h2>
            <p class="project-fund-dialog__lead"><?= esc(lang('Projects.fund_form_lead')) ?></p>
        </header>

        <?= view('front/projects/partials/fund_form', [
            'project'          => $project,
            'fundPostUrl'      => project_fund_post_url($slug),
            'showFundBudget'   => $showFundBudget,
            'showFundMaterial' => $showFundMaterial,
            'fundSuccess'      => $fundSuccess,
            'fundErrors'       => $fundErrors,
        ]) ?>
    </div>
</dialog>
