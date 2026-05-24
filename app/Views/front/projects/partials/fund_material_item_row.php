<?php

declare(strict_types=1);

/** @var int $rowIndex */
/** @var string $itemVal */
/** @var string $qtyVal */
/** @var bool $canRemove */

$rowIndex  = (int) ($rowIndex ?? 0);
$itemVal   = (string) ($itemVal ?? '');
$qtyVal    = (string) ($qtyVal ?? '');
$canRemove = (bool) ($canRemove ?? false);
?>
<div class="project-fund-form__item-row" data-fund-item-row>
    <div class="project-fund-form__item-inputs">
        <div class="project-fund-form__item-input project-fund-form__item-input--name">
            <input
                type="text"
                class="form-control"
                id="fund-material-item-<?= $rowIndex ?>"
                name="material_item_name[]"
                maxlength="255"
                value="<?= esc($itemVal) ?>"
                placeholder="<?= esc(lang('Projects.fund_field_items_placeholder'), 'attr') ?>"
                aria-label="<?= esc(lang('Projects.fund_field_items'), 'attr') ?>"
                data-fund-item-name
                data-fund-validate-input
            >
        </div>
        <div class="project-fund-form__item-input project-fund-form__item-input--qty">
            <input
                type="text"
                class="form-control project-fund-form__item-qty-input"
                id="fund-material-qty-<?= $rowIndex ?>"
                name="material_item_qty[]"
                maxlength="120"
                value="<?= esc($qtyVal) ?>"
                placeholder="<?= esc(lang('Projects.fund_field_quantity_placeholder'), 'attr') ?>"
                aria-label="<?= esc(lang('Projects.fund_field_quantity'), 'attr') ?>"
                data-fund-item-qty
                data-fund-validate-input
            >
        </div>
        <div class="project-fund-form__item-input project-fund-form__item-input--remove">
            <?php if ($canRemove) : ?>
                <button type="button" class="project-fund-form__item-remove" data-fund-item-remove aria-label="<?= esc(lang('Projects.fund_remove_item'), 'attr') ?>">×</button>
            <?php else : ?>
                <span class="project-fund-form__item-remove-spacer" aria-hidden="true"></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="project-fund-form__item-errors">
        <p class="project-fund-form__field-error" data-fund-item-name-error role="alert" hidden></p>
        <p class="project-fund-form__field-error" data-fund-item-qty-error role="alert" hidden></p>
        <span class="project-fund-form__item-errors-spacer" aria-hidden="true"></span>
    </div>
</div>
