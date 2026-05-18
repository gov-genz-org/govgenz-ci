<?php

declare(strict_types=1);

helper(['project', 'language']);

/** @var array<string, mixed> $project */
/** @var string $fundPostUrl */
/** @var bool $showFundBudget */
/** @var bool $showFundMaterial */
/** @var string|null $fundSuccess */
/** @var list<string> $fundErrors */

$fundSuccess = $fundSuccess ?? null;
$fundErrors  = $fundErrors ?? [];
if (! is_array($fundErrors)) {
    $fundErrors = [];
}

$showFundBudget   = $showFundBudget ?? false;
$showFundMaterial = $showFundMaterial ?? false;
if (! $showFundBudget && ! $showFundMaterial) {
    return;
}

$defaultType = (string) old('contribution_type', $showFundBudget ? 'budget' : 'material');
if ($defaultType !== 'budget' && $defaultType !== 'material') {
    $defaultType = $showFundBudget ? 'budget' : 'material';
}
if ($defaultType === 'budget' && ! $showFundBudget) {
    $defaultType = 'material';
}
if ($defaultType === 'material' && ! $showFundBudget && ! $showFundMaterial) {
    $defaultType = 'budget';
}
$offerBudget   = $showFundBudget;
$offerMaterial = $showFundMaterial || $showFundBudget;
$showTypePicker = $offerBudget && $offerMaterial;
$budgetDisplay   = trim((string) ($project['budget_display'] ?? ''));
$materialLines   = project_fund_material_lines_from_old_input();
$fundPhoneMsgs = [
    'generic' => lang('Site.join_phone_invalid'),
    '1'       => lang('Site.join_phone_err_country'),
    '2'       => lang('Site.join_phone_err_short'),
    '3'       => lang('Site.join_phone_err_long'),
    '4'       => lang('Site.join_phone_err_local'),
    '5'       => lang('Site.join_phone_err_length'),
];
$fundFieldMsgs = [
    'budget_donor_name'        => [
        'required'   => lang('Projects.fund_validation_name_required'),
        'max_length' => lang('Projects.fund_validation_name_max'),
    ],
    'budget_amount'              => [
        'required'   => lang('Projects.fund_validation_amount_required'),
        'invalid'    => lang('Projects.fund_validation_amount_invalid'),
        'max_length' => lang('Projects.fund_validation_amount_max'),
    ],
    'budget_remarks'             => ['max_length' => lang('Projects.fund_validation_remarks_max')],
    'material_donor_name'      => [
        'required'   => lang('Projects.fund_validation_name_required'),
        'max_length' => lang('Projects.fund_validation_name_max'),
    ],
    'material_items'             => [
        'required'   => lang('Projects.fund_validation_items_required'),
        'max_length' => lang('Projects.fund_validation_items_max'),
    ],
    'material_quantity'          => [
        'required'   => lang('Projects.fund_validation_quantity_required'),
        'max_length' => lang('Projects.fund_validation_quantity_max'),
    ],
    'material_pickup_location'   => ['max_length' => lang('Projects.fund_validation_pickup_max')],
    'material_remarks'           => ['max_length' => lang('Projects.fund_validation_remarks_max')],
    'phone_required'             => lang('Projects.fund_validation_phone_required'),
];
$fundValidateRules = [
    'budget' => [
        ['input' => 'fund-budget-name', 'error' => 'fund-budget-name-error', 'name' => 'budget_donor_name', 'required' => true, 'max' => 255],
        ['input' => 'fund-budget-amount', 'error' => 'fund-budget-amount-error', 'name' => 'budget_amount', 'required' => true, 'max' => 120, 'amount' => true],
        ['input' => 'fund-budget-remarks', 'error' => 'fund-budget-remarks-error', 'name' => 'budget_remarks', 'max' => 4000],
    ],
    'material' => [
        ['input' => 'fund-material-name', 'error' => 'fund-material-name-error', 'name' => 'material_donor_name', 'required' => true, 'max' => 255],
        ['input' => 'fund-material-pickup', 'error' => 'fund-material-pickup-error', 'name' => 'material_pickup_location', 'max' => 255],
        ['input' => 'fund-material-remarks', 'error' => 'fund-material-remarks-error', 'name' => 'material_remarks', 'max' => 4000],
    ],
];
?>
<form
    method="post"
    action="<?= esc($fundPostUrl, 'attr') ?>"
    class="project-fund-form__form"
    data-project-fund-form
    data-fund-phone-msgs="<?= esc(json_encode($fundPhoneMsgs, JSON_UNESCAPED_UNICODE), 'attr') ?>"
    data-fund-field-msgs="<?= esc(json_encode($fundFieldMsgs, JSON_UNESCAPED_UNICODE), 'attr') ?>"
    data-fund-validate-rules="<?= esc(json_encode($fundValidateRules, JSON_UNESCAPED_UNICODE), 'attr') ?>"
    data-fund-remove-label="<?= esc(lang('Projects.fund_remove_item'), 'attr') ?>"
    novalidate
>
    <?= csrf_field() ?>
    <?php if ($fundSuccess !== null) : ?>
        <p class="project-fund-dialog__alert project-fund-dialog__alert--success" role="status"><?= esc($fundSuccess) ?></p>
    <?php endif; ?>
    <?php if ($fundErrors !== []) : ?>
        <div class="project-fund-dialog__alert project-fund-dialog__alert--error" role="alert" data-fund-errors>
            <ul>
                <?php foreach ($fundErrors as $err) : ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <input type="hidden" name="contribution_type" value="<?= esc($defaultType, 'attr') ?>" data-fund-type-input>

    <div class="project-fund-form__scroll">
    <?php if ($showTypePicker) : ?>
        <fieldset class="project-fund-form__type" data-fund-type-picker>
            <legend class="project-fund-form__type-legend"><?= esc(lang('Projects.fund_form_type_legend')) ?></legend>
            <div class="project-fund-form__type-options">
                <?php if ($offerBudget) : ?>
                    <label class="project-fund-form__type-option">
                        <input type="radio" class="project-fund-form__type-radio" name="contribution_type_ui" value="budget" <?= $defaultType === 'budget' ? 'checked' : '' ?> data-fund-type-radio>
                        <span class="project-fund-form__type-label"><?= esc(lang('Projects.fund_form_type_budget')) ?></span>
                    </label>
                <?php endif; ?>
                <?php if ($offerMaterial) : ?>
                    <label class="project-fund-form__type-option">
                        <input type="radio" class="project-fund-form__type-radio" name="contribution_type_ui" value="material" <?= $defaultType === 'material' ? 'checked' : '' ?> data-fund-type-radio>
                        <span class="project-fund-form__type-label"><?= esc(lang('Projects.fund_form_type_material')) ?></span>
                    </label>
                <?php endif; ?>
            </div>
        </fieldset>
    <?php else : ?>
        <p class="project-fund-form__type-legend"><?= esc(lang('Projects.fund_form_type_legend')) ?></p>
        <p class="project-fund-form__type-single"><?= esc($showFundBudget ? lang('Projects.fund_form_type_budget') : lang('Projects.fund_form_type_material')) ?></p>
    <?php endif; ?>

    <div data-fund-panel="budget" class="project-fund-form__panel"<?= $defaultType !== 'budget' ? ' hidden' : '' ?>>
        <?php if ($budgetDisplay !== '') : ?>
            <p class="project-fund-form__hint"><?= esc(lang('Projects.fund_form_budget_hint', ['budget' => $budgetDisplay])) ?></p>
        <?php endif; ?>
        <div class="project-fund-form__field">
            <label class="form-label" for="fund-budget-name"><?= esc(lang('Projects.fund_field_name')) ?></label>
            <input type="text" class="form-control" id="fund-budget-name" name="budget_donor_name" required maxlength="255" value="<?= esc(old('budget_donor_name', old('donor_name', ''))) ?>" data-fund-validate-input>
            <p class="project-fund-form__field-error" id="fund-budget-name-error" role="alert" hidden></p>
        </div>
        <?= view('front/projects/partials/fund_phone_field', ['prefix' => 'budget']) ?>
        <div class="project-fund-form__field">
            <label class="form-label" for="fund-budget-amount"><?= esc(lang('Projects.fund_field_amount')) ?></label>
            <input type="text" class="form-control" id="fund-budget-amount" name="budget_amount" maxlength="120" value="<?= esc(old('budget_amount', '')) ?>" placeholder="<?= esc(lang('Projects.fund_field_amount_placeholder'), 'attr') ?>" data-fund-validate-input required>
            <p class="project-fund-form__field-error" id="fund-budget-amount-error" role="alert" hidden></p>
        </div>
        <div class="project-fund-form__field">
            <label class="form-label" for="fund-budget-remarks"><?= esc(lang('Projects.fund_field_remarks')) ?></label>
            <textarea class="form-control" id="fund-budget-remarks" name="budget_remarks" rows="3" maxlength="4000" data-fund-validate-input><?= esc(old('budget_remarks', old('remarks', ''))) ?></textarea>
            <p class="project-fund-form__field-error" id="fund-budget-remarks-error" role="alert" hidden></p>
        </div>
    </div>

    <div data-fund-panel="material" class="project-fund-form__panel"<?= $defaultType !== 'material' ? ' hidden' : '' ?>>
        <div class="project-fund-form__field">
            <label class="form-label" for="fund-material-name"><?= esc(lang('Projects.fund_field_name')) ?></label>
            <input type="text" class="form-control" id="fund-material-name" name="material_donor_name" required maxlength="255" value="<?= esc(old('material_donor_name', old('donor_name', ''))) ?>" data-fund-validate-input>
            <p class="project-fund-form__field-error" id="fund-material-name-error" role="alert" hidden></p>
        </div>
        <div class="project-fund-form__field project-fund-form__field--items" data-fund-items-field>
            <span class="form-label project-fund-form__items-title"><?= esc(lang('Projects.fund_items_list_title')) ?></span>
            <p class="project-fund-form__hint project-fund-form__hint--tight"><?= esc(lang('Projects.fund_field_items_hint')) ?></p>
            <p class="project-fund-form__field-error" id="fund-material-items-list-error" data-fund-items-list-error role="alert" hidden></p>
            <div class="project-fund-form__items-table">
                <div class="project-fund-form__items-table-head" aria-hidden="true">
                    <span><?= esc(lang('Projects.fund_field_items')) ?></span>
                    <span><?= esc(lang('Projects.fund_field_quantity')) ?></span>
                    <span></span>
                </div>
                <div class="project-fund-form__items-list" data-fund-items-list>
                <?php foreach ($materialLines as $mi => $line) :
                    echo view('front/projects/partials/fund_material_item_row', [
                        'rowIndex'  => $mi,
                        'itemVal'   => (string) ($line['item'] ?? ''),
                        'qtyVal'    => (string) ($line['qty'] ?? ''),
                        'canRemove' => $mi > 0,
                    ]);
                endforeach; ?>
                </div>
            </div>
            <template id="fund-material-item-template">
                <?= view('front/projects/partials/fund_material_item_row', [
                    'rowIndex'  => '__INDEX__',
                    'itemVal'   => '',
                    'qtyVal'    => '',
                    'canRemove' => true,
                ]) ?>
            </template>
            <button type="button" class="project-fund-form__items-add projects-program-show__btn projects-program-show__btn--ghost" data-fund-items-add><?= esc(lang('Projects.fund_add_item')) ?></button>
        </div>

        <div class="project-fund-form__field">
            <label class="form-label" for="fund-material-available"><?= esc(lang('Projects.fund_field_available')) ?></label>
            <input type="date" class="form-control" id="fund-material-available" name="material_available_from" value="<?= esc(old('material_available_from', old('available_from', ''))) ?>">
        </div>
        <div class="project-fund-form__field">
            <label class="form-label" for="fund-material-pickup"><?= esc(lang('Projects.fund_field_pickup')) ?></label>
            <input type="text" class="form-control" id="fund-material-pickup" name="material_pickup_location" maxlength="255" value="<?= esc(old('material_pickup_location', old('pickup_location', ''))) ?>" data-fund-validate-input>
            <p class="project-fund-form__field-error" id="fund-material-pickup-error" role="alert" hidden></p>
        </div>
        <?= view('front/projects/partials/fund_phone_field', ['prefix' => 'material']) ?>
        <div class="project-fund-form__field">
            <label class="form-label" for="fund-material-deliver"><?= esc(lang('Projects.fund_field_delivery')) ?></label>
            <select class="form-select" id="fund-material-deliver" name="material_can_deliver">
                <option value=""><?= esc(lang('Projects.fund_delivery_choose')) ?></option>
                <option value="1"<?= old('material_can_deliver') === '1' ? ' selected' : '' ?>><?= esc(lang('Projects.fund_delivery_yes')) ?></option>
                <option value="0"<?= old('material_can_deliver') === '0' ? ' selected' : '' ?>><?= esc(lang('Projects.fund_delivery_no')) ?></option>
            </select>
        </div>
        <div class="project-fund-form__field">
            <label class="form-label" for="fund-material-remarks"><?= esc(lang('Projects.fund_field_remarks')) ?></label>
            <textarea class="form-control" id="fund-material-remarks" name="material_remarks" rows="3" maxlength="4000" data-fund-validate-input><?= esc(old('material_remarks', old('remarks', ''))) ?></textarea>
            <p class="project-fund-form__field-error" id="fund-material-remarks-error" role="alert" hidden></p>
        </div>
    </div>

    </div>

    <footer class="project-fund-form__footer project-fund-form__actions">
        <button type="button" class="projects-program-show__btn projects-program-show__btn--ghost" data-fund-modal-close><?= esc(lang('Projects.fund_modal_cancel')) ?></button>
        <button type="submit" class="projects-program-show__btn projects-program-show__btn--teal"><?= esc(lang('Projects.fund_form_submit')) ?></button>
    </footer>
</form>
